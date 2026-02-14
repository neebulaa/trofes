import Layout from "../Layouts/Layout";
import "../../css/Recipes.css";
import { useForm, router, Link, usePage } from "@inertiajs/react";
import { useEffect, useRef, useState, useMemo, useCallback } from "react";
import RecipeCard from "../Components/RecipeCard";
import Paginator from "../Components/Paginator";
import Dropdown from "../Components/Dropdown";
import NotFoundSection from "../Components/NotFoundSection";

export default function Recipes({
    recipes,
    hero_recipes,
    recommended_recipes,
    recipe_filter_options = [],
    active_filter = null,
    is_custom_mode = false,
    custom_filters = null,
    ingredient_options = [],
    diet_options = [],
    allergy_options = [],
}) {
    const { url } = usePage();
    const { data, setData, errors } = useForm({ search: "" });
    const {
        auth: { user },
    } = usePage().props;

    const [activeIndex, setActiveIndex] = useState(0);
    const [isVisible, setIsVisible] = useState(false);
    const timersRef = useRef({ intervalId: null, timeoutId: null });

    const SHOW_MS = 5000;
    const FADE_MS = 450;

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

    useEffect(() => {
        const u = new URL(url, window.location.origin);
        const q = u.searchParams.get("search") ?? "";
        setData((prev) => ({ ...prev, search: q }));
    }, [url, setData]);

    const categoryOptions = useMemo(
        () => [
            { label: "Latest", value: "latest" },
            { label: "Oldest", value: "oldest" },
            { label: "A - Z", value: "alphabetical" },
            { label: "Z - A", value: "reverse-alphabetical" },
        ],
        [],
    );

    const [category, setCategory] = useState(categoryOptions[0]);

    // keep dropdown synced with URL ?sort=
    useEffect(() => {
        const u = new URL(url, window.location.origin);
        const sort = u.searchParams.get("sort") ?? "none";
        const found =
            categoryOptions.find((o) => o.value === sort) ?? categoryOptions[0];
        setCategory(found);
    }, [url, categoryOptions]);

    // navigation helper (merge query params)
    const navigateWithMergedQuery = useCallback(
        (updates) => {
            const u = new URL(url, window.location.origin);
            const params = Object.fromEntries(u.searchParams.entries());

            Object.entries(updates || {}).forEach(([key, value]) => {
                if (value === undefined || value === null || value === "") {
                    delete params[key];
                } else {
                    params[key] = String(value);
                }
            });

            router.get("/recipes", params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [url],
    );

    function handleSubmit(e) {
        e.preventDefault();
        navigateWithMergedQuery({
            search: data.search,
            page: undefined,
        });
    }

    function handlePillClick(pill) {
        if (pill.type === "all") {
            navigateWithMergedQuery({
                filter_type: undefined,
                filter_id: undefined,
                page: undefined,
            });
            return;
        }

        navigateWithMergedQuery({
            filter_type: pill.type,
            filter_id: pill.id ?? undefined,
            page: undefined,
        });
    }

    function clearFilter() {
        navigateWithMergedQuery({
            filter_type: undefined,
            filter_id: undefined,
            page: undefined,
        });
    }

    function clearCustomMode() {
        router.get(
            "/recipes",
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    const displayRecipes = useMemo(() => {
        return Array.isArray(recipes?.data) ? recipes.data : [];
    }, [recipes]);

    const hasQuery = useMemo(() => {
        const u = new URL(url, window.location.origin);
        return u.searchParams.toString().length > 0;
    }, [url]);

    const showRecommended =
        user && recommended_recipes?.length > 0 && !hasQuery && !is_custom_mode;

    // lookup maps for custom filters banner
    const ingredientLabelById = useMemo(
        () =>
            new Map(
                (ingredient_options || []).map((o) => [
                    Number(o.value),
                    o.label,
                ]),
            ),
        [ingredient_options],
    );
    const dietLabelById = useMemo(
        () =>
            new Map(
                (diet_options || []).map((o) => [Number(o.value), o.label]),
            ),
        [diet_options],
    );
    const allergyLabelById = useMemo(
        () =>
            new Map(
                (allergy_options || []).map((o) => [Number(o.value), o.label]),
            ),
        [allergy_options],
    );

    const customModeDetails = useMemo(() => {
        if (!is_custom_mode || !custom_filters) return null;

        const ingIds = Array.isArray(custom_filters.ingredients)
            ? custom_filters.ingredients
            : [];
        const dietIds = Array.isArray(custom_filters.dietary_preferences)
            ? custom_filters.dietary_preferences
            : [];
        const allergyIds = Array.isArray(custom_filters.allergies)
            ? custom_filters.allergies
            : [];

        const ingredients = ingIds
            .map((id) => ingredientLabelById.get(Number(id)) ?? `#${id}`)
            .filter(Boolean);

        const diets = dietIds
            .map((id) => dietLabelById.get(Number(id)) ?? `#${id}`)
            .filter(Boolean);

        const allergies = allergyIds
            .map((id) => allergyLabelById.get(Number(id)) ?? `#${id}`)
            .filter(Boolean);

        return {
            ingredients,
            diets,
            allergies,
            macros: {
                calories: custom_filters.calories || null,
                carbohydrate: custom_filters.carbohydrate || null,
                protein: custom_filters.protein || null,
                fat: custom_filters.fat || null,
            },
        };
    }, [
        is_custom_mode,
        custom_filters,
        ingredientLabelById,
        dietLabelById,
        allergyLabelById,
    ]);

    return (
        <section id="recipes-page" className="recipes-page">
            <div className="container">
                <div className="recipes-page-hero">
                    <div className="recipes-page-hero-left">
                        <h1 className="recipes-page-hero-title">
                            Discover the Best Food{" "}
                            <span className="green-block">Recipes</span> in the
                            World
                        </h1>
                        <p className="recipes-page-hero-desc">
                            Discover the Best Food Recipes in the World helps
                            users find a variety of selected dishes from
                            different countries.
                        </p>

                        <form
                            onSubmit={handleSubmit}
                            className="mt-2 recipes-search-form-first"
                        >
                            <div className="input-group">
                                <div className="search-input">
                                    <span>
                                        <i className="fa-solid fa-magnifying-glass"></i>
                                    </span>

                                    <input
                                        type="text"
                                        value={data.search}
                                        onChange={(e) =>
                                            setData("search", e.target.value)
                                        }
                                        placeholder="Search recipes..."
                                    />

                                    <button
                                        type="submit"
                                        className="search-btn"
                                    >
                                        Search
                                    </button>
                                </div>

                                {errors.search && (
                                    <small className="error-text">
                                        {errors.search}
                                    </small>
                                )}
                            </div>
                        </form>
                    </div>

                    <div className="recipes-page-hero-right">
                        <div className="recipe-show">
                            {card && (
                                <Link
                                    href={`/recipes/${card.slug}`}
                                    style={{ display: "block" }}
                                    className={`recipe-show-card ${isVisible ? "in" : "out"}`}
                                    key={card.recipe_id}
                                >
                                    <img
                                        src={card.public_image}
                                        alt={card.title}
                                    />
                                    <p className="recipe-card-badge for-name">
                                        <span className="badge-text">
                                            {card.title}
                                        </span>
                                    </p>
                                </Link>
                            )}
                        </div>
                    </div>
                </div>

                <form
                    onSubmit={handleSubmit}
                    className="mt-2 recipes-search-form-second"
                >
                    <div className="input-group">
                        <div className="search-input">
                            <span>
                                <i className="fa-solid fa-magnifying-glass"></i>
                            </span>

                            <input
                                type="text"
                                value={data.search}
                                onChange={(e) =>
                                    setData("search", e.target.value)
                                }
                                placeholder="Search guides..."
                            />

                            <button type="submit" className="search-btn">
                                Search
                            </button>
                        </div>

                        {errors.search && (
                            <small className="error-text">
                                {errors.search}
                            </small>
                        )}
                    </div>
                </form>

                {/* Custom mode banner */}
                {is_custom_mode && (
                    <div className="mt-2 custom-mode-banner">
                        <div className="custom-mode-banner__inner">
                            <div className="custom-mode-banner__content">
                                <p className="custom-mode-banner__title">
                                    Custom Mode is active
                                </p>

                                {customModeDetails && (
                                    <div className="custom-mode-banner__details">
                                        {customModeDetails.ingredients.length >
                                            0 && (
                                            <p className="custom-mode-banner__row">
                                                <span className="banner-row-title">
                                                    Ingredients:
                                                </span>{" "}
                                                {customModeDetails.ingredients.join(
                                                    ", ",
                                                )}
                                            </p>
                                        )}

                                        {customModeDetails.diets.length > 0 && (
                                            <p className="custom-mode-banner__row">
                                                <span className="banner-row-title">
                                                    Diets:
                                                </span>{" "}
                                                {customModeDetails.diets.join(
                                                    ", ",
                                                )}
                                            </p>
                                        )}

                                        {customModeDetails.allergies.length >
                                            0 && (
                                            <p className="custom-mode-banner__row">
                                                <span className="banner-row-title">
                                                    Excluded Allergies:
                                                </span>{" "}
                                                {customModeDetails.allergies.join(
                                                    ", ",
                                                )}
                                            </p>
                                        )}

                                        {(customModeDetails.macros.calories ||
                                            customModeDetails.macros
                                                .carbohydrate ||
                                            customModeDetails.macros.protein ||
                                            customModeDetails.macros.fat) && (
                                            <p className="custom-mode-banner__row">
                                                <span className="banner-row-title">
                                                    Macros:
                                                </span>{" "}
                                                {[
                                                    customModeDetails.macros
                                                        .calories
                                                        ? `Calories ± ${customModeDetails.macros.calories}`
                                                        : null,
                                                    customModeDetails.macros
                                                        .carbohydrate
                                                        ? `Carb ± ${customModeDetails.macros.carbohydrate}`
                                                        : null,
                                                    customModeDetails.macros
                                                        .protein
                                                        ? `Protein ± ${customModeDetails.macros.protein}`
                                                        : null,
                                                    customModeDetails.macros.fat
                                                        ? `Fat ± ${customModeDetails.macros.fat}`
                                                        : null,
                                                ]
                                                    .filter(Boolean)
                                                    .join(" • ")}
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>

                            <button
                                type="button"
                                className="btn btn-line-white btn-rounded"
                                onClick={clearCustomMode}
                                title="Exit custom mode"
                            >
                                Clear Custom Mode
                            </button>
                        </div>
                    </div>
                )}

                {showRecommended && (
                    <>
                        <h2 className="recipes-container-title mt-2">
                            Recommended For You
                        </h2>
                        <div className="recipes-container mt-1">
                            {recommended_recipes.map((recipe) => (
                                <RecipeCard
                                    recipe={recipe}
                                    key={recipe.recipe_id}
                                />
                            ))}
                        </div>
                    </>
                )}

                {showRecommended && (
                    <div className="custom-search-navigator mt-2">
                        <Link
                            href="/custom-search-recipes"
                            type="button"
                            className="btn btn-fill btn-rounded"
                        >
                            <i className="fa-brands fa-searchengin"></i>
                            <p>Use Custom Search</p>
                        </Link>

                        <p className="text-muted">
                            Search according to your own preferences and needs
                            with 'Custom Search'.
                        </p>
                    </div>
                )}

                <div className="recipe-filters mt-2">
                    <div className="filters-sort">
                        <span className="filters-text">Filter by:</span>
                        <Dropdown
                            options={categoryOptions}
                            value={category}
                            onChange={(next) => {
                                setCategory(next);
                                navigateWithMergedQuery({
                                    sort:
                                        next.value === "none"
                                            ? undefined
                                            : next.value,
                                    page: undefined,
                                });
                            }}
                        />
                    </div>

                    <div className="filter-pills">
                        {recipe_filter_options.map((pill) => {
                            const isActive = (() => {
                                const activeType = active_filter?.type ?? "all";
                                const activeId = active_filter?.id ?? null;

                                if (pill.type === "all")
                                    return activeType === "all";
                                if (pill.type === "popular")
                                    return activeType === "popular";
                                if (pill.id == null || activeId == null)
                                    return false;

                                return (
                                    activeType === pill.type &&
                                    Number(activeId) === Number(pill.id)
                                );
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

                        {active_filter?.type &&
                            active_filter.type !== "all" && (
                                <button
                                    type="button"
                                    className="pill"
                                    onClick={clearFilter}
                                    title="Clear filter"
                                >
                                    Clear
                                </button>
                            )}
                    </div>
                </div>

                {displayRecipes.length === 0 ? (
                    <NotFoundSection message="No recipes found." />
                ) : (
                    <>
                        {showRecommended && (
                            <h2 className="recipes-container-title mt-2">
                                All Recipes
                            </h2>
                        )}

                        {is_custom_mode && (
                            <h2 className="recipes-container-title mt-2">
                                Custom Search Results
                            </h2>
                        )}

                        <div className="recipes-container mt-1">
                            {displayRecipes.map((recipe) => (
                                <RecipeCard
                                    recipe={recipe}
                                    key={recipe.recipe_id}
                                    isCustomMode={is_custom_mode}
                                />
                            ))}
                        </div>
                    </>
                )}

                <Paginator
                    paginator={recipes}
                    onNavigate={(to) =>
                        router.get(
                            to,
                            {},
                            { preserveState: true },
                        )
                    }
                />
            </div>
        </section>
    );
}

Recipes.layout = (page) => <Layout children={page} />;