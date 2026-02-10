import Layout from "../Layouts/Layout";
import { useState } from "react";
import "../../css/NutrientsCalculator.css";
import { Link, useForm, usePage } from "@inertiajs/react";
import RecipeCard from "../Components/RecipeCard";

export default function NutrientsCalculator({
    recommended_recipes = [],
    user_age,
}) {
    const { data, setData, post, processing, errors } = useForm({
        activity_level: "MIDDLE", // LOW | MIDDLE | HIGH | VERY HIGH
        gender: "male",
        age: user_age || "",
        weight: "",
        height: "",
        goal: "MAINTAIN", // LOSE | MAINTAIN | GAIN | MUSCLE

        calories: null,
        carbs_g: null,
        protein_g: null,
        fat_g: null,
        carbs_pct: null,
        protein_pct: null,
        fat_pct: null,
    });

    const { props } = usePage();
    const {
        auth: { user },
    } = props;

    const [calculated, setCalculated] = useState(false);

    const activityLevels = [
        {
            id: "low",
            label: "Low",
            value: "LOW",
            description:
                "Mostly sedentary lifestyle with little to no physical activity (e.g., desk or office job).",
        },
        {
            id: "middle",
            label: "Middle",
            value: "MIDDLE",
            description:
                "Moderately active lifestyle with regular exercise 3-5 days per week or light physical work.",
        },
        {
            id: "high",
            label: "High",
            value: "HIGH",
            description:
                "Active lifestyle with intense exercise or training 6-7 days per week.",
        },
        {
            id: "very-high",
            label: "Very High",
            value: "VERY HIGH",
            description:
                "Very active lifestyle involving daily intense exercise or a physically demanding job (e.g., athletes or manual labor).",
        },
    ];

    const activityMultiplierByLevel = {
        LOW: 1.2,
        MIDDLE: 1.55,
        HIGH: 1.725,
        "VERY HIGH": 1.9,
    };

    const macroRatioByGoal = {
        LOSE: { carbs: 0.4, protein: 0.3, fat: 0.3 },
        MAINTAIN: { carbs: 0.5, protein: 0.25, fat: 0.25 },
        GAIN: { carbs: 0.5, protein: 0.2, fat: 0.3 },
        MUSCLE: { carbs: 0.4, protein: 0.3, fat: 0.3 },
    };

    function clampNumber(value, min, max) {
        const n = Number(value);
        if (!Number.isFinite(n)) return null;
        return Math.min(max, Math.max(min, n));
    }

    function calcBmr({ gender, age, weightKg, heightCm }) {
        // Mifflin–St Jeor
        const base = 10 * weightKg + 6.25 * heightCm - 5 * age;
        return gender === "female" ? base - 161 : base + 5;
    }

    function calcTargets(formData) {
        const age = clampNumber(formData.age, 1, 120);
        const weightKg = clampNumber(formData.weight, 1, 500);
        const heightCm = clampNumber(formData.height, 30, 300);

        if (age === null || weightKg === null || heightCm === null) return null;

        const bmr = calcBmr({
            gender: formData.gender,
            age,
            weightKg,
            heightCm,
        });

        const multiplier =
            activityMultiplierByLevel[formData.activity_level] ?? 1.55;
        const tdee = bmr * multiplier;

        const goalAdjustByGoal = {
            LOSE: -0.2, // -20%
            MAINTAIN: 0,
            GAIN: 0.15, // +15%
            MUSCLE: 0.1, // +10%
        };

        const adjustedCalories =
            tdee * (1 + (goalAdjustByGoal[formData.goal] ?? 0));
        const calories = Math.round(adjustedCalories);

        const ratios =
            macroRatioByGoal[formData.goal] ?? macroRatioByGoal.MAINTAIN;

        const carbsKcal = calories * ratios.carbs;
        const proteinKcal = calories * ratios.protein;
        const fatKcal = calories * ratios.fat;

        const carbs_g = Math.round(carbsKcal / 4);
        const protein_g = Math.round(proteinKcal / 4);
        const fat_g = Math.round(fatKcal / 9);

        return {
            calories,
            carbs_g,
            protein_g,
            fat_g,
            carbs_pct: ratios.carbs,
            protein_pct: ratios.protein,
            fat_pct: ratios.fat,
        };
    }

    function handleSubmit(e) {
        e.preventDefault();

        const t = calcTargets(data);
        if (!t) return;

        setData({
            ...data,
            ...t,
        });

        post("/nutrients-calculator", {
            preserveScroll: true,
            transform: (formData) => ({
                ...formData,
                ...t,
            }),
            onSuccess: () => {
                setCalculated(true);
            },
            // preserveState: true
        });
    }

    function resetForm() {
        setData({
            activity_level: "MIDDLE",
            gender: "male",
            age: user_age || "",
            weight: "",
            height: "",
            goal: "MAINTAIN",

            calories: null,
            carbs_g: null,
            protein_g: null,
            fat_g: null,
            carbs_pct: null,
            protein_pct: null,
            fat_pct: null,
        });
    }

    return (
        <>
            <section
                className={
                    "nutrients-calculator" + (calculated ? " calculated" : "")
                }
                id="nutrients-calculator"
            >
                <div className="nutrients-calculator-left">
                    <div className="container">
                        <img
                            src="./assets/graphics/crown.png"
                            alt="crown graphics"
                            className="nutrients-title-graphic"
                        />
                        <h1 className="nutrients-title">
                            <span className="green-block">Nutrients</span>{" "}
                            <br />
                            Calculator
                        </h1>
                        <p className="nutrients-description">
                            Calculate optimal macronutrients ratios for your
                            body with our easy-to-use calculator.
                        </p>
                    </div>
                </div>

                <div className="nutrients-main">
                    <div className="calculator">
                        <div className="container">
                            <form action="" onSubmit={handleSubmit}>
                                <div className="calculator-input">
                                    <h2 className="subsection-title">
                                        Body Parameter
                                    </h2>
                                    <p>
                                        Input your Gender, Age, Weight, Height
                                    </p>

                                    <div className="gender-inputs mt-1">
                                        <div className="gender-choice selected">
                                            <input
                                                type="radio"
                                                hidden
                                                id="male"
                                                name="gender"
                                                checked={data.gender === "male"}
                                                onChange={() =>
                                                    setData("gender", "male")
                                                }
                                            />
                                            <label
                                                htmlFor="male"
                                                className="gender-selection"
                                            >
                                                Male
                                            </label>
                                        </div>
                                        <div className="gender-choice">
                                            <input
                                                type="radio"
                                                hidden
                                                id="female"
                                                name="gender"
                                                checked={
                                                    data.gender === "female"
                                                }
                                                onChange={() =>
                                                    setData("gender", "female")
                                                }
                                            />
                                            <label
                                                htmlFor="female"
                                                className="gender-selection"
                                            >
                                                Female
                                            </label>
                                        </div>
                                    </div>

                                    <div className="body-parameters-inputs mt-1">
                                        <div className="body-parameter">
                                            <input
                                                min="1"
                                                max="120"
                                                id="age"
                                                type="number"
                                                placeholder="Age (years)"
                                                className="body-parameter-input"
                                                value={data.age}
                                                onChange={(e) => {
                                                    setData(
                                                        "age",
                                                        e.target.value,
                                                    );
                                                }}
                                            />
                                            {errors.age && (
                                                <span className="error-text">
                                                    {errors.age}
                                                </span>
                                            )}
                                        </div>

                                        <div className="body-parameter">
                                            <div className="input-group-identifier">
                                                <input
                                                    min="1"
                                                    max="500"
                                                    id="weight"
                                                    type="number"
                                                    placeholder="Weight (kg)"
                                                    className="body-parameter-input"
                                                    value={data.weight}
                                                    onChange={(e) => {
                                                        setData(
                                                            "weight",
                                                            e.target.value,
                                                        );
                                                    }}
                                                />
                                                <span className="identifier">
                                                    kg
                                                </span>
                                            </div>
                                            {errors.weight && (
                                                <span className="error-text">
                                                    {errors.weight}
                                                </span>
                                            )}
                                        </div>

                                        <div className="body-parameter">
                                            <div className="input-group-identifier">
                                                <input
                                                    min="30"
                                                    max="300"
                                                    id="height"
                                                    type="number"
                                                    placeholder="Height (cm)"
                                                    className="body-parameter-input"
                                                    value={data.height}
                                                    onChange={(e) => {
                                                        setData(
                                                            "height",
                                                            e.target.value,
                                                        );
                                                    }}
                                                />
                                                <span className="identifier">
                                                    cm
                                                </span>
                                            </div>
                                            {errors.height && (
                                                <small className="error-text">
                                                    {errors.height}
                                                </small>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="calculator-input">
                                    <h2 className="subsection-title">
                                        Activity Level
                                    </h2>
                                    {errors.activity_level && (
                                        <small className="error-text mb-05">
                                            {errors.activity_level}
                                        </small>
                                    )}
                                    <p>
                                        <span style={{ fontWeight: "500" }}>
                                            {
                                                activityLevels.find(
                                                    (level) =>
                                                        level.value ===
                                                        data.activity_level,
                                                )?.label
                                            }
                                        </span>
                                        :{" "}
                                        {
                                            activityLevels.find(
                                                (level) =>
                                                    level.value ===
                                                    data.activity_level,
                                            )?.description
                                        }
                                    </p>

                                    <div className="activity-level-inputs">
                                        <div className="activity-level-track">
                                            <div
                                                className="activity-level-indicator"
                                                style={{
                                                    left: `${
                                                        activityLevels.findIndex(
                                                            (level) =>
                                                                level.value ===
                                                                data.activity_level,
                                                        ) *
                                                        (100 /
                                                            (activityLevels.length -
                                                                1))
                                                    }%`,
                                                }}
                                            >
                                                <div className="indicator-dot"></div>
                                            </div>
                                        </div>

                                        <div className="activity-level-labels">
                                            {activityLevels.map(
                                                (level, index) => (
                                                    <div
                                                        key={level.id}
                                                        className={`activity-level-label ${
                                                            data.activity_level ===
                                                            level.value
                                                                ? "active"
                                                                : ""
                                                        }`}
                                                        onClick={() =>
                                                            setData(
                                                                "activity_level",
                                                                level.value,
                                                            )
                                                        }
                                                        style={{
                                                            left: `${
                                                                index *
                                                                (100 /
                                                                    (activityLevels.length -
                                                                        1))
                                                            }%`,
                                                        }}
                                                    >
                                                        <span>
                                                            {level.label}
                                                        </span>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>

                                    <div className="nutrients-calculator-buttons">
                                        <button
                                            className="btn btn-fill"
                                            type="button"
                                            onClick={resetForm}
                                            disabled={processing}
                                        >
                                            Reset
                                        </button>
                                        <button
                                            className="btn btn-outline"
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Calculate
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div className="nutrients-calculator-right">
                        <div className="container">
                            <h2 className="subsection-title">Your Result</h2>

                            <div className="calculator-result">
                                <div className="calculator-result-calories">
                                    <h2>
                                        {data.calories
                                            ? `${data.calories} kcal`
                                            : "-- kcal"}
                                    </h2>
                                    <p>
                                        Suggested amount of calories{" "}
                                        <b>per day</b>.
                                    </p>
                                </div>

                                <div className="calculator-result-list">
                                    <div className="calculator-result-list-item">
                                        <h4>Carbohydrate</h4>
                                        <p>
                                            <span>
                                                {data.carbs_g
                                                    ? `${data.carbs_g} g`
                                                    : "-- g"}
                                            </span>{" "}
                                            /
                                            {data.carbs_pct
                                                ? `${(data.carbs_pct * 100).toFixed(0)}%`
                                                : "--%"}
                                        </p>
                                    </div>

                                    <div className="calculator-result-list-item">
                                        <h4>Protein</h4>
                                        <p>
                                            <span>
                                                {data.protein_g
                                                    ? `${data.protein_g} g`
                                                    : "-- g"}
                                            </span>{" "}
                                            /
                                            {data.protein_pct
                                                ? `${(data.protein_pct * 100).toFixed(0)}%`
                                                : "--%"}
                                        </p>
                                    </div>

                                    <div className="calculator-result-list-item">
                                        <h4>Fat</h4>
                                        <p>
                                            <span>
                                                {data.fat_g
                                                    ? `${data.fat_g} g`
                                                    : "-- g"}
                                            </span>{" "}
                                            /
                                            {data.fat_pct
                                                ? `${(data.fat_pct * 100).toFixed(0)}%`
                                                : "--%"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="calculator-goals">
                                <h2 className="subsection-title">
                                    Adjust according to your goals
                                </h2>
                                {errors.goal && (
                                    <small className="error-text mb-05">
                                        {errors.goal}
                                    </small>
                                )}

                                <div className="calculator-goals-buttons">
                                    <button
                                        type="button"
                                        className={
                                            data.goal === "LOSE"
                                                ? "selected"
                                                : ""
                                        }
                                        onClick={() => setData("goal", "LOSE")}
                                    >
                                        Lose Weight
                                    </button>
                                    <button
                                        type="button"
                                        className={
                                            data.goal === "MAINTAIN"
                                                ? "selected"
                                                : ""
                                        }
                                        onClick={() =>
                                            setData("goal", "MAINTAIN")
                                        }
                                    >
                                        Maintain Weight
                                    </button>
                                    <button
                                        type="button"
                                        className={
                                            data.goal === "GAIN"
                                                ? "selected"
                                                : ""
                                        }
                                        onClick={() => setData("goal", "GAIN")}
                                    >
                                        Gain Weight
                                    </button>
                                    <button
                                        type="button"
                                        className={
                                            data.goal === "MUSCLE"
                                                ? "selected"
                                                : ""
                                        }
                                        onClick={() =>
                                            setData("goal", "MUSCLE")
                                        }
                                    >
                                        Gain Muscle
                                    </button>
                                </div>
                            </div>

                            {recommended_recipes.length > 0 && (
                                <div className="recommended-recipes">
                                    <div className="subsection-split">
                                        <h2 className="subsection-title">
                                            Recommended Recipes
                                        </h2>
                                        <Link
                                            href="/recipes"
                                            className="color-primary medium"
                                        >
                                            See All
                                        </Link>
                                    </div>

                                    <div className="recommended-recipes-list">
                                        <div className="recommended-recipes-list-wrapper">
                                            <div className="recipes-container">
                                                {recommended_recipes.map(
                                                    (recipe) => (
                                                        <RecipeCard
                                                            key={
                                                                recipe.recipe_id
                                                            }
                                                            recipe={recipe}
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

NutrientsCalculator.layout = (page) => <Layout children={page} />;
