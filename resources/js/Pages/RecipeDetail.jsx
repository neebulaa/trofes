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
            </div>
            <div className="recipe-detail-content">
                <h2>Instructions</h2>
                <p>{recipe.instructions}</p>
            </div>
        </div>
    );
}

RecipeDetail.layout = (page) => <Layout children={page} />;
