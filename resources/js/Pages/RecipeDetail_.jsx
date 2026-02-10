import Layout from "../Layouts/Layout";
import "../../css/RecipeDetail.css";

export default function RecipeDetail({ recipe, user }) {
    return (
        <div className="recipe-detail-page container">
            <h1 className="recipe-detail-title">{recipe.title}</h1>
            <div className="recipe-detail-image">
                <img src={recipe.public_image} alt={recipe.title} />
            </div>
            <div className="recipe-detail-info">
                <p><strong>Rating:</strong> {(Math.round(recipe.rating * 10) / 10).toFixed(1)}</p>
                <p><strong>Cooking Time:</strong> {recipe.cooking_time} mins</p>
                <p><strong>Total Ingredients:</strong> {recipe.total_ingredient} ingreds</p>
                <p><strong>Total Likes:</strong> {recipe.likes_count}</p>
                <p><strong>Calories</strong>: {recipe.calories}kcal</p>
                <p><strong>Protein</strong>: {recipe.protein}gr</p>
                <p><strong>Fat</strong>: {recipe.fat}gr</p>
                <p><strong>Sodium</strong>: {recipe.sodium}mg</p>
            </div>

            {recipe.measured_ingredients && (
                <div className="recipe-detail-content">
                    <h3>Measured Ingredients</h3>
                    <p>{recipe.measured_ingredients}</p>
                </div>
            )}

            {recipe.dietary_preferences.length > 0 && (
                <div className="recipe-detail-content mt-1">
                    <h3>Dietary Preferences</h3>
                    <p>{recipe.dietary_preferences.map(dp => dp.diet_name).join(', ')}</p>
                </div>
            )}

            {recipe.allergies.length > 0 && (
                <div className="recipe-detail-content mt-1">
                    <h3>Allergies</h3>
                    <p>{recipe.allergies.map(a => a.allergy_name).join(', ')}</p>
                </div>
            )}

            <div className="recipe-detail-content mt-2">
                <h2>Instructions</h2>
                <p>{recipe.instructions}</p>
            </div>
        </div>
    );
}

RecipeDetail.layout = (page) => <Layout children={page} />;
