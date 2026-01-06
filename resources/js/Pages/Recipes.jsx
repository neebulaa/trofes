import Layout from "../Layouts/Layout";
import "../../css/Recipes.css";
import { useForm } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";
import RecipeCard from "../Components/RecipeCard";
import Paginator from "../Components/Paginator";
import { router } from "@inertiajs/react";
import { Link } from "@inertiajs/react";

const cardsData = [
    { id: 1, img: "/assets/sample-images/nasi hainan.jpg", label: "Nasi Hainan Tiongkok" },
    { id: 2, img: "/assets/sample-images/sate ayam.jpg", label: "Sate Ayam" },
    { id: 3, img: "/assets/sample-images/nasi lemak.jpg", label: "Nasi Lemak Malaysia" },
    { id: 4, img: "/assets/sample-images/ramen sapi.jpg", label: "Beef Ramen" },
    { id: 5, img: "/assets/sample-images/chicken masala.jpg", label: "Chicken Masala" },
];

export default function Recipes({recipes, hero_recipes, recommended_recipes}) {
    const { data, setData, errors } = useForm({ search: "" });

    const [activeIndex, setActiveIndex] = useState(0);
    const [isVisible, setIsVisible] = useState(false);
    const timersRef = useRef({ intervalId: null, timeoutId: null });

    const SHOW_MS = 5000;
    const FADE_MS = 450;  // samain dgn CSS transition duration

    useEffect(() => {
        const firstIn = setTimeout(() => setIsVisible(true), 0);

        timersRef.current.intervalId = setInterval(() => {
            setIsVisible(false);

            timersRef.current.timeoutId = setTimeout(() => {
                setActiveIndex((prev) => (prev + 1) % hero_recipes.length);
                requestAnimationFrame(() => setIsVisible(true));
            }, FADE_MS);
        }, SHOW_MS);

        return () => {
            clearTimeout(firstIn);
            clearInterval(timersRef.current.intervalId);
            clearTimeout(timersRef.current.timeoutId);
        };
    }, []);

    const card = hero_recipes[activeIndex];

    return (
        <section id="recipes-page" className="recipes-page">
        <div className="container">
            <div className="recipes-page-hero">
                <div className="recipes-page-hero-left">
                    <h1 className="recipes-page-hero-title">
                        Discover the Best Food <span className="green-block">Recipes</span> in the World
                    </h1>
                    <p className="recipes-page-hero-desc">
                        Discover the Best Food Recipes in the World helps users find a variety of selected dishes from different countries.
                    </p>

                    <form action="" className="mt-2 recipes-search-form-first">
                        <div className="input-group">
                            <div className="search-input">
                                <span>
                                    <i className="fa-solid fa-magnifying-glass"></i>
                                </span>

                                <input
                                    type="text"
                                    value={data.search}
                                    onChange={(e) => setData("search", e.target.value)}
                                    placeholder="Search guides..."
                                />

                                <button type="submit" className="search-btn">
                                    Search
                                </button>
                            </div>

                            {errors.search && <small className="error-text">{errors.search}</small>}
                        </div>
                    </form>
                </div>

                <div className="recipes-page-hero-right">
                    <div className="recipe-show">
                        <div className={`recipe-show-card ${isVisible ? "in" : "out"}`} key={card.recipe_id}>
                            <img src={card.public_image} alt={card.title} />
                            <p className="recipe-card-badge for-name">
                                <span className="badge-text">{card.title}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="" className="mt-2 recipes-search-form-second">
                <div className="input-group">
                    <div className="search-input">
                        <span>
                            <i className="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input
                            type="text"
                            value={data.search}
                            onChange={(e) => setData("search", e.target.value)}
                            placeholder="Search guides..."
                        />

                        <button type="submit" className="search-btn">
                            Search
                        </button>
                    </div>

                    {errors.search && <small className="error-text">{errors.search}</small>}
                </div>
            </form>

            <h2 className="recipes-container-title mt-2">Recommended For You</h2>
            <div className="recipes-container mt-1">
                {recommended_recipes.map((recipe) => (
                    <RecipeCard recipe={recipe} key={recipe.recipe_id}/>
                ))}
            </div>

            <div className="custom-search-navigator mt-3">
                <Link
                    href="/custom-search-recipes"
                    type="button"
                    className="btn btn-fill btn-rounded"
                >
                    <i className="fa-brands fa-searchengin"></i>
                    <p>Use Custom Search</p>
                </Link>

                <p className="text-muted">Search according to your own preferences and needs with 'Custom Search'.</p>
            </div>

            <h2 className="recipes-container-title mt-3">All Recipes</h2>
            <div className="recipes-container mt-1">
                {recipes.data.map((recipe) => (
                    <RecipeCard recipe={recipe} key={recipe.recipe_id}/>
                ))}
            </div>

            <Paginator paginator={recipes} onNavigate={(url) => router.get(url)} />
        </div>
        </section>
    );
}

Recipes.layout = (page) => <Layout children={page} />;
