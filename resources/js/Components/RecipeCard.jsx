import {Link} from '@inertiajs/react';

export default function Recipe({recipe}){
    return (
        <Link key={recipe.id} className="recipe-card" href={`/recipes/${recipe.slug}`}>
            <div className="recipe-image">
                <img
                    src={recipe.public_image}
                    alt={recipe.image}
                    className="profile-liked-recipe-image"
                />

                {
                    recipe.is_favorite && 
                    <span className="recipe-card-badge favs">
                        <i className="fa-solid fa-heart"></i>
                        <span className="badge-text">Popular</span>
                    </span>
                }
            </div>
            <div className="recipe-content">
                <h3 className="profile-liked-recipe-title">{recipe.title}</h3>
                <div className="recipe-info">
                    <div className="recipe-rating recipe-info-list">
                        <i className="fa-solid fa-star"></i>
                        <p>{(Math.round(recipe.rating * 10) / 10).toFixed(1)}</p>
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
    )
}