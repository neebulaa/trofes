import Layout from "../Layouts/Layout";
import "../../css/RecipeDetail.css";
import { useState, useEffect } from "react";
import FlashMessage from "../Components/FlashMessage";
import CookingTimer from "../PagesComponent/RecipeDetail/CookingTimer";

function csrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
}

export default function RecipeDetail({ recipe, user }) {
    const [liked, setLiked] = useState(!!recipe.liked_by_me);
    const [likesCount, setLikesCount] = useState(recipe.likes_count ?? 0);
    const [busy, setBusy] = useState(false);

    function formatCookingTime(totalMinutes) {
        let mins = Math.max(0, Math.floor(Number(totalMinutes) || 0));

        const MIN_PER_HOUR = 60;
        const MIN_PER_DAY = 60 * 24;
        const MIN_PER_WEEK = MIN_PER_DAY * 7;
        const MIN_PER_MONTH = MIN_PER_DAY * 30;
        const MIN_PER_YEAR = MIN_PER_DAY * 365;

        const years = Math.floor(mins / MIN_PER_YEAR);
        mins -= years * MIN_PER_YEAR;

        const months = Math.floor(mins / MIN_PER_MONTH);
        mins -= months * MIN_PER_MONTH;

        const weeks = Math.floor(mins / MIN_PER_WEEK);
        mins -= weeks * MIN_PER_WEEK;

        const days = Math.floor(mins / MIN_PER_DAY);
        mins -= days * MIN_PER_DAY;

        const hours = Math.floor(mins / MIN_PER_HOUR);
        mins -= hours * MIN_PER_HOUR;

        const parts = [];
        if (years) parts.push(`${years} yr`);
        if (months) parts.push(`${months} mo`);
        if (weeks) parts.push(`${weeks} wk`);
        if (days) parts.push(`${days} d`);
        if (hours) parts.push(`${hours} hrs`);

        // include minutes if there are leftover minutes OR nothing else to show
        if (mins || parts.length === 0) parts.push(`${mins} mins`);

        return parts.join(" ");
    }

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
            // rollback
            setLiked(!nextLiked);
            setLikesCount((c) => c + (nextLiked ? -1 : 1));
            console.error(err);
        } finally {
            setBusy(false);
        }
    }

    const [yt, setYt] = useState({
        loading: true,
        embedUrl: null,
        error: null,
    });

    useEffect(() => {
        let cancelled = false;

        async function loadYoutube() {
            try {
                setYt({ loading: true, embedUrl: null, error: null });

                // just tweak the query to improve results
                const query = `how to make ${recipe.title} recipe`;

                const res = await fetch(
                    `/youtube/search?q=${encodeURIComponent(query)}`,
                );
                const data = await res.json();

                if (cancelled) return;

                if (!res.ok) {
                    setYt({
                        loading: false,
                        embedUrl: null,
                        error: data?.error || "Failed to load video",
                    });
                    return;
                }

                setYt({ loading: false, embedUrl: data.embedUrl, error: null });
            } catch (e) {
                if (!cancelled)
                    setYt({
                        loading: false,
                        embedUrl: null,
                        error: "Failed to load video",
                    });
            }
        }

        loadYoutube();
        return () => {
            cancelled = true;
        };
    }, [recipe.title]);

    function splitIngredientsSmart(text) {
        if (!text) return [];
        const s = text.replace(/\s+/g, " ").trim();

        // without a qty number start
        const noQtyStarts = [
            "freshly",
            "ground",
            "salt",
            "pepper",
            "kosher",
            "black",
            "white",
            "olive oil",
            "oil",
            "butter",
        ];

        const parts = s.split(
            new RegExp(
                String.raw`,\s+(?=(?:\d+\s+\d+\/\d+|\d+\/\d+|\d+|a|an)\b|(?:${noQtyStarts
                    .map((x) => x.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"))
                    .join("|")})\b)`,
                "i",
            ),
        );

        return parts.map((p) => p.trim()).filter(Boolean);
    }

    function stripLeadingStepNumber(s) {
        return s
            .replace(/^\s*(?:step\s*)?\d+\s*(?:[.)-]|:)\s*/i, "") // "1. " / "2) " / "Step 3: "
            .trim();
    }

    function splitByNumberedList(text) {
        // normalize line endings
        const s = String(text).replace(/\r\n/g, "\n").trim();

        // detect if it looks like a numbered recipe (has many "1." / "2." / "3." starts)
        const matches =
            s.match(/(?:^|\n)\s*(?:step\s*)?\d+\s*[.)-]\s+/gi) ?? [];
        const looksNumbered = matches.length >= 2;

        if (!looksNumbered) return null;

        // split when a new numbered item starts (beginning or after newline)
        const raw = s.split(/(?:^|\n)\s*(?=(?:step\s*)?\d+\s*[.)-]\s+)/i);

        const cleaned = raw
            .map((part) => stripLeadingStepNumber(part))
            .map((part) =>
                part.replace(/\n+/g, " ").replace(/\s+/g, " ").trim(),
            )
            .filter(Boolean);

        return cleaned.length ? cleaned : null;
    }

    function splitAfterParen(step) {
        const parts = step.split(/\)\s+(?=[A-Z0-9])/g);
        if (parts.length <= 1) return [step];
        return parts.map((p, i) =>
            i < parts.length - 1 ? p.trim() + ")" : p.trim(),
        );
    }

    function splitInstructionsSmart(text) {
        if (!text) return [];

        // if the text is clearly a numbered list, use that first
        const numbered = splitByNumberedList(text);
        if (numbered) {
            // optional to still split each numbered step into sentences if want smaller steps:
            // return numbered.flatMap(splitInstructionsSmart);
            return numbered;
        }

        // otherwise fallback to sentence splitting
        let s = String(text)
            .replace(/\r\n/g, "\n")
            .replace(/\u00A0/g, " ")
            .replace(/[ \t]+/g, " ")
            .replace(/\n+/g, " ")
            .trim();

        // remove stray "1." at start (when not caught by numbered list)
        s = s.replace(/^\s*(?:step\s*)?\d+\s*[.)-]\s*/i, "");

        // protect decimals: 1.5
        s = s.replace(/(\d)\.(\d)/g, "$1<dot>$2");

        // protect punctuation inside parentheses
        s = s.replace(/\(([^)]*)\)/g, (full, inner) => {
            const safe = inner
                .replace(/\./g, "<p>")
                .replace(/\!/g, "<e>")
                .replace(/\?/g, "<q>");
            return `(${safe})`;
        });

        let parts = s.split(/(?<=[.!?])\s+(?=[A-Z0-9(])/g);

        parts = parts
            .map((x) =>
                x
                    .replace(/<dot>/g, ".")
                    .replace(/<p>/g, ".")
                    .replace(/<e>/g, "!")
                    .replace(/<q>/g, "?")
                    .trim(),
            )
            .filter(Boolean);

        // merge parenthetical-only sentences into previous
        const merged = [];
        for (const p of parts) {
            if (p.startsWith("(") && merged.length)
                merged[merged.length - 1] += ` ${p}`;
            else merged.push(p);
        }

        // split any step that has ") <NewSentence>"
        return merged
            .flatMap(splitAfterParen)
            .map((x) => x.trim())
            .filter(Boolean);
    }

    return (
        <>
            <FlashMessage className="flash-screen" />
            <div className="recipe-detail-page">
                <CookingTimer />
                <div className="container">
                    <div className="recipe-detail-header">
                        <div className="logo-stamp">
                            <img
                                src="/assets/logo/logo-stamp-transparent.png"
                                alt="Logo Stamp"
                            />
                        </div>
                        <div className="recipe-detail-image">
                            <img src={recipe.public_image} alt={recipe.title} />

                            <button
                                className="btn btn-rounded recipe-detail-like-button"
                                type="button"
                                onClick={likeRecipe}
                                disabled={busy}
                                aria-label={
                                    liked ? "Unlike recipe" : "Like recipe"
                                }
                            >
                                {liked ? (
                                    <i className="fa-solid fa-heart" />
                                ) : (
                                    <i className="fa-regular fa-heart" />
                                )}
                                {liked ? "You liked this" : "Like this recipe"}
                            </button>
                        </div>

                        <div className="recipe-detail-content-box">
                            <h1 className="recipe-detail-title">
                                {recipe.title}
                            </h1>
                            <p className="mt-05">
                                {recipe.dietary_preferences.length > 0 &&
                                    recipe.dietary_preferences
                                        .map((d) => d.diet_name)
                                        .join(", ")}

                                {recipe.dietary_preferences.length > 0 &&
                                    recipe.allergies.length > 0 && (
                                        <span className="color-primary ml-05 mr-05">
                                            •
                                        </span>
                                    )}

                                {recipe.allergies.length > 0 &&
                                    "Warning contains: " +
                                        recipe.allergies
                                            .map((a) => a.allergy_name)
                                            .join(", ")}
                            </p>

                            <div className="recipe-detail-infos">
                                <div className="recipe-detail-info">
                                    <div className="recipe-detail-info-icon">
                                        <i className="fa-solid fa-heart" />
                                    </div>
                                    <div className="recipe-detail-info-text">
                                        <p className="recipe-detail-info-label">
                                            Favorites
                                        </p>
                                        <p className="recipe-detail-info-value">
                                            {likesCount}
                                        </p>
                                    </div>
                                </div>
                                <div className="recipe-detail-info">
                                    <div className="recipe-detail-info-icon">
                                        <i className="fa-regular fa-clock"></i>
                                    </div>
                                    <div className="recipe-detail-info-text">
                                        <p className="recipe-detail-info-label">
                                            Cooking Time
                                        </p>
                                        <p className="recipe-detail-info-value">
                                            {formatCookingTime(
                                                recipe.cooking_time,
                                            )}
                                        </p>
                                    </div>
                                </div>
                                <div className="recipe-detail-info">
                                    <div className="recipe-detail-info-icon">
                                        <i className="fa-solid fa-leaf"></i>
                                    </div>
                                    <div className="recipe-detail-info-text">
                                        <p className="recipe-detail-info-label">
                                            Total Ingredients
                                        </p>
                                        <p className="recipe-detail-info-value">
                                            {recipe.total_ingredient}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="recipe-detail-nutrients recipe-detail-section">
                        <h3>Food Nutrients (Makro)</h3>

                        <div className="recipe-detail-nutrient-cards">
                            <div className="recipe-detail-nutrient-card">
                                <div className="recipe-detail-nutrient-icon">
                                    <i className="fa-solid fa-fire-flame-curved"></i>
                                </div>
                                <h4>Calories</h4>
                                <p>{recipe.calories} kcal</p>
                            </div>
                            <div className="recipe-detail-nutrient-card">
                                <div className="recipe-detail-nutrient-icon">
                                    <i class="fa-solid fa-bowl-food"></i>
                                </div>
                                <h4>Carbohydrate</h4>
                                <p>{recipe.carbohydrate} gr</p>
                            </div>
                            <div className="recipe-detail-nutrient-card">
                                <div className="recipe-detail-nutrient-icon">
                                    <i className="fa-solid fa-drumstick-bite" />
                                </div>
                                <h4>Protein</h4>
                                <p>{recipe.protein} gr</p>
                            </div>
                            <div className="recipe-detail-nutrient-card">
                                <div className="recipe-detail-nutrient-icon">
                                    <i className="fa-solid fa-cookie"></i>
                                </div>
                                <h4>Fat</h4>
                                <p>{recipe.fat} gr</p>
                            </div>
                        </div>
                    </div>

                    <div className="recipe-detail-ingr-inst">
                        <div className="recipe-detail-ingredients recipe-detail-section">
                            <h3>Measured Ingredients</h3>
                            <ul>
                                {splitIngredientsSmart(
                                    recipe.measured_ingredients,
                                ).map((ingr, index) => (
                                    <li key={index}>{ingr}</li>
                                ))}
                            </ul>
                        </div>

                        <div className="recipe-detail-instructions recipe-detail-section">
                            <h3>Instructions</h3>
                            {splitInstructionsSmart(recipe.instructions).map(
                                (step, index) => (
                                    <div className="mb-1" key={index}>
                                        <p className="color-primary medium">
                                            Step {index + 1}
                                        </p>
                                        <p>{step}</p>
                                    </div>
                                ),
                            )}
                            <div className="recipe-detail-youtube">
                                <h3>Video Tutorial</h3>
                                <div className="recipe-detail-youtube-video">
                                    {yt.loading && <p>Loading video...</p>}

                                    {!yt.loading && yt.error && (
                                        <p className="error-text">{yt.error}</p>
                                    )}

                                    {!yt.loading &&
                                        !yt.error &&
                                        yt.embedUrl && (
                                            <iframe
                                                width="100%"
                                                height="500"
                                                src={yt.embedUrl}
                                                title="YouTube video player"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowFullScreen
                                            />
                                        )}

                                    {!yt.loading &&
                                        !yt.error &&
                                        !yt.embedUrl && (
                                            <p>
                                                No video found for this recipe.
                                            </p>
                                        )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

RecipeDetail.layout = (page) => <Layout children={page} />;
