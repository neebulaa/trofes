import { Link } from "@inertiajs/react";
import { useState } from "react";

function csrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
}

export default function Recipe({ recipe }) {
    const [liked, setLiked] = useState(!!recipe.liked_by_me);
    const [likesCount, setLikesCount] = useState(recipe.likes_count ?? 0);
    const [busy, setBusy] = useState(false);

    async function likeRecipe(e) {
        e.preventDefault();
        e.stopPropagation();

        if (busy) return;

        const nextLiked = !liked;

        setLiked(nextLiked);
        setLikesCount((c) => c + (nextLiked ? 1 : -1));
        setBusy(true);

        try {
            const res = await fetch(`/recipes/${recipe.slug}/like`, {
                method: nextLiked ? "POST" : "DELETE",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                },
            });

            if (res.status === 401) {
                throw new Error("Unauthenticated");
            }

            if (!res.ok) {
                throw new Error("Request failed");
            }

            const json = await res.json();
            setLiked(!!json.is_liked);
            setLikesCount(json.likes_count ?? likesCount);
        } catch (err) {
            setLiked(!nextLiked);
            setLikesCount((c) => c + (nextLiked ? -1 : 1));
            console.error(err);
        } finally {
            setBusy(false);
        }
    }

    return (
        <Link className="recipe-card" href={`/recipes/${recipe.slug}`}>
            <div className="recipe-image">
                <img
                    src={recipe.public_image}
                    alt={recipe.image}
                    className="profile-liked-recipe-image"
                />
                <div className="recipe-image-overlay" />

                <button
                    type="button"
                    className="recipe-card-like-button"
                    onClick={likeRecipe}
                    disabled={busy}
                    aria-label={liked ? "Unlike recipe" : "Like recipe"}
                >
                    {liked ? (
                        <i className="fa-solid fa-heart" />
                    ) : (
                        <i className="fa-regular fa-heart" />
                    )}
                </button>

                {recipe.is_favorite && (
                    <span className="recipe-card-badge favs">
                        <i className="fa-solid fa-heart"></i>
                        <span className="badge-text">Popular</span>
                    </span>
                )}
            </div>

            <div className="recipe-content">
                <h3 className="profile-liked-recipe-title">{recipe.title}</h3>

                <div className="recipe-info">
                    <div className="recipe-ingredients-count recipe-info-list">
                        <i className="fa-solid fa-heart"></i>
                        <p>{likesCount} likes</p>
                    </div>
                    
                    <div className="recipe-cooking-time recipe-info-list">
                        <i className="fa-regular fa-clock"></i>
                        <p>{recipe.cooking_time} mins</p>
                    </div>

                    <div className="recipe-ingredients-count recipe-info-list">
                        <i className="fa-solid fa-leaf"></i>
                        <p>{recipe.total_ingredient} ingreds</p>
                    </div>
                </div>
            </div>
        </Link>
    );
}
