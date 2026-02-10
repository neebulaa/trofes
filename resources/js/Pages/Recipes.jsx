import Layout from "../Layouts/Layout";
import "../../css/Recipes.css";
import { useForm, router, Link, usePage } from "@inertiajs/react";
import { useEffect, useRef, useState, useMemo } from "react";
import RecipeCard from "../Components/RecipeCard";
import Paginator from "../Components/Paginator";
import Dropdown from "../Components/Dropdown";
import NotFoundSection from "../Components/NotFoundSection";

export default function Recipes({recipes, hero_recipes, recommended_recipes, recipe_filter_options = [], active_filter = null}) {
    const { url } = usePage();
    const { data, setData, errors } = useForm({ search: "" });

    const [activeIndex, setActiveIndex] = useState(0);
    const [isVisible, setIsVisible] = useState(false);
    const timersRef = useRef({ intervalId: null, timeoutId: null });

    const SHOW_MS = 5000;
    const FADE_MS = 450;

    useEffect(() => {
        const u = new URL(url, window.location.origin);
        const q = u.searchParams.get("search") ?? "";
        setData((prev) => ({
            ...prev,
            search: q,
        }));
    }, [url]);

    useEffect(() => {
        if (!hero_recipes?.length) return;
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
    }, [hero_recipes?.length]);

    const card = hero_recipes?.[activeIndex];

    const categoryOptions = [
        {label: 'None', value: 'none'},
        { label: "Latest", value: "latest" },
        { label: "Oldest", value: "oldest" },
        { label: "A - Z", value: "alphabetical" },
        { label: "Z - A", value: "reverse-alphabetical" },
    ];

    const [category, setCategory] = useState(categoryOptions[0]);

    function handleSubmit(e) {
        e.preventDefault();

        // keep current filter when searching
        router.get(
        "/recipes",
        {
            search: data.search,
            filter_type: active_filter?.type ?? undefined,
            filter_id: active_filter?.id ?? undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true }
        );
    }

    function handlePillClick(pill) {
        router.get("/recipes", {
                search: data.search,
                filter_type: pill.type === "all" ? undefined : pill.type,
                filter_id: pill.type === "all" ? undefined : (pill.id ?? undefined)
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }

    function clearFilter() {
        router.get(
        "/recipes",
        { search: data.search },
        { preserveState: true, preserveScroll: true, replace: true }
        );
    }

    const displayRecipes = useMemo(() => {
        const items = Array.isArray(recipes?.data) ? [...recipes.data] : [];

        switch (category.value) {
            case "latest":
                return items.sort(
                    (a, b) => new Date(b.created_at) - new Date(a.created_at)
                );

            case "oldest":
                return items.sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );

            case "alphabetical":
                return items.sort((a, b) =>
                    String(a.title).localeCompare(String(b.title), "id", {
                        sensitivity: "base",
                    })
                );

            case "reverse-alphabetical":
                return items.sort((a, b) =>
                    String(b.title).localeCompare(String(a.title), "id", {
                        sensitivity: "base",
                    })
                );

            default:
                return items;
        }
    }, [recipes, category]);

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

                    <form onSubmit={handleSubmit} className="mt-2 recipes-search-form-first">
                        <div className="input-group">
                            <div className="search-input">
                                <span>
                                    <i className="fa-solid fa-magnifying-glass"></i>
                                </span>

                                <input
                                    type="text"
                                    value={data.search}
                                    onChange={(e) => setData("search", e.target.value)}
                                    placeholder="Search recipes..."
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
                        <Link href={`/recipes/${card.slug}`} style={{ display: 'block' }} className={`recipe-show-card ${isVisible ? "in" : "out"}`} key={card.recipe_id}>
                            <img src={card.public_image} alt={card.title} />
                            <p className="recipe-card-badge for-name">
                                <span className="badge-text">{card.title}</span>
                            </p>
                        </Link>
                    </div>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="mt-2 recipes-search-form-second">
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

            <div className="recipe-filters mt-3">
                <div className="filters-sort">
                    <span className="filters-text">
                        Filter by:
                    </span>
                    <Dropdown
                        options={categoryOptions}
                        value={category}
                        onChange={setCategory}
                    />
                </div>

                <div className="filter-pills">
                    {recipe_filter_options.map((pill) => {
                        const isActive = (() => {
                            const activeType = active_filter?.type ?? "all";
                            const activeId = active_filter?.id ?? null;

                            if (pill.type === "all") return activeType === "all";
                            if (pill.type === "popular") return activeType === "popular";
                            if (pill.id == null || activeId == null) return false;
                            return activeType === pill.type && Number(activeId) === Number(pill.id);
                        })();

                        return (
                            <button
                                key={pill.key}
                                type="button"
                                className={`pill ${isActive ? "active" : ""}`}
                                onClick={() => handlePillClick(pill)}
                                title={pill.label}
                            >
                                {pill.label}
                            </button>
                        );
                    })}
                </div>
            </div>

            {
                displayRecipes.length === 0 ?
                    <NotFoundSection message="No recipes found." />
                : <>
                    <h2 className="recipes-container-title mt-3">All Recipes</h2>
                    <div className="recipes-container mt-1">
                        {displayRecipes.map((recipe) => (
                            <RecipeCard recipe={recipe} key={recipe.recipe_id}/>
                        ))}
                    </div>
                </>
            }

            <Paginator paginator={recipes} onNavigate={(url) => router.get(url)} />
        </div>
        </section>
    );
}

Recipes.layout = (page) => <Layout children={page} />;
