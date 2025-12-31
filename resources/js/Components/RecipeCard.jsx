export default function Recipe({recipe}){
    return (
        <div key={recipe.id} className="recipe-card">
            <div className="recipe-image">
                <img
                    src={recipe.public_image}
                    alt={recipe.image}
                    className="profile-liked-recipe-image"
                />
                {/* <span className="recipe-tags">
                    <i class="fa-solid fa-crown"></i>
                    <p>
                        Premium
                    </p>
                </span> */}
                {/* <span className="recipe-tags">
                    <i class="fa-solid fa-heart"></i>
                    <p>
                        Favs
                    </p>
                </span> */}
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
        </div>
    )
}