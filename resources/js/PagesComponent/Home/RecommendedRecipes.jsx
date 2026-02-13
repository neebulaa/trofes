import RecipeCard from "../../Components/RecipeCard";
import { Link } from "@inertiajs/react";

export default function RecommendedRecipes({recipes}) {
    return (
        <div className="container">
            <div className="hero-recommended-split mt-2">
                <div className="hero-recommended-text">
                    <h2 className="home-recommended-title">
                        Food Recommended For You
                    </h2>
                    <p className="hero-recommended-desc">Discover delicious recipes tailored to your taste preferences.</p>
                </div>
                <Link className="btn btn-line" href="/recipes">View All</Link>
            </div>
            <div className="recipes-container mt-1">
                {recipes.map((recipe) => (
                    <RecipeCard
                        recipe={recipe}
                        key={recipe.recipe_id}
                    />
                ))}
            </div>
        </div>
    );
}