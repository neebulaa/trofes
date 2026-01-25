import Layout from "../Layouts/Layout";
import "../../css/CustomSearchRecipes.css";
import CustomDatalist from "../Components/CustomDatalist";
import { useState, useMemo, useRef } from "react";
import { useForm } from "@inertiajs/react";
import CameraCaptureModal from "../Components/CameraCaptureModal";

export default function CustomSearchRecipes({
    allergies,
    dietary_preferences,
    user_allergies,
    user_dietary_preferences,
    user,
    ingredients,
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        allergies: user_allergies || [],
        dietary_preferences: user_dietary_preferences || [],
        ingredients: [],
        calories: "",
        protein: "",
        fat: "",
        sodium: "",
    });

    const [selectedAllergies, setSelectedAllergies] = useState(
        (user_allergies || []).map((a) => ({
            value: a,
            label:
                allergies.find((allergy) => allergy.allergy_id === a)
                    ?.allergy_name || "",
        })),
    );

    const [selectedDietaryPreferences, setSelectedDietaryPreferences] =
        useState(
            (user_dietary_preferences || []).map((d) => ({
                value: d,
                label:
                    dietary_preferences.find(
                        (dp) => dp.dietary_preference_id === d,
                    )?.diet_name || "",
            })),
        );

    const [selectedIngredients, setSelectedIngredients] = useState([]);
    const [cameraMenuOpen, setCameraMenuOpen] = useState(false);
    const [showIngredientsCamera, setShowIngredientsCamera] = useState(false);
    const [isInferencing, setIsInferencing] = useState(false);

    const fileInputRef = useRef(null);
    const cameraInputRef = useRef(null);

    const ingredientLookup = useMemo(() => {
        return new Map(
            (ingredients || []).map((i) => [
                i.ingredient_name.toLowerCase(),
                i,
            ]),
        );
    }, [ingredients]);

    function handleIngredientsChange(newValues) {
        setSelectedIngredients(newValues);
        setData(
            "ingredients",
            newValues.map((v) => v.value),
        );
    }

    function handleDietaryPreferencesChange(newValues) {
        setSelectedDietaryPreferences(newValues);
        setData(
            "dietary_preferences",
            newValues.map((v) => v.value),
        );
    }

    function handleAllergyChange(newValues) {
        setSelectedAllergies(newValues);
        setData(
            "allergies",
            newValues.map((v) => v.value),
        );
    }

    function handleSubmit(e) {
        e.preventDefault();

        post("/custom-search-recipes", {
            preserveScroll: true,
            onSuccess: () => {
                // reset();
            },
        });
    }

    function handleReset() {
        reset();
        setSelectedIngredients([]);
        setSelectedAllergies(
            (user_allergies || []).map((a) => ({
                value: a,
                label:
                    allergies.find((allergy) => allergy.allergy_id === a)
                        ?.allergy_name || "",
            })),
        );
        setSelectedDietaryPreferences(
            (user_dietary_preferences || []).map((d) => ({
                value: d,
                label:
                    dietary_preferences.find(
                        (dp) => dp.dietary_preference_id === d,
                    )?.diet_name || "",
            })),
        );

        setData((prev) => ({
            ...prev,
            allergies: user_allergies || [],
            dietary_preferences: user_dietary_preferences || [],
            ingredients: [],
            calories: "",
            protein: "",
            fat: "",
            sodium: "",
        }));
    }

    function canUseCamera() {
        return (
            typeof navigator !== "undefined" &&
            navigator.mediaDevices &&
            navigator.mediaDevices.getUserMedia
        );
    }

    async function detectIngredientsFromImage(file) {
        if (!file) return;

        setIsInferencing(true);

        try {
            const formData = new FormData();
            formData.append("file", file);

            const response = await fetch(
                "https://bagusarya-trofes-ingredients-detection.hf.space/detect",
                {
                    method: "POST",
                    body: formData,
                },
            );

            if (!response.ok) return;

            const data = await response.json();

            if (data.status !== "success" || !Array.isArray(data.ingredients)) {
                return;
            }

            const selectedIds = new Set(
                selectedIngredients.map((item) => item.value),
            );

            const newSelections = data.ingredients
                .map((name) =>
                    ingredientLookup.get(String(name).toLowerCase().trim()),
                )
                .filter(Boolean)
                .filter((item) => !selectedIds.has(item.ingredient_id))
                .map((item) => ({
                    value: item.ingredient_id,
                    label:
                        item.ingredient_name[0].toUpperCase() +
                        item.ingredient_name.slice(1),
                }));

            if (newSelections.length > 0) {
                const updated = [...selectedIngredients, ...newSelections];
                setSelectedIngredients(updated);
                setData(
                    "ingredients",
                    updated.map((v) => v.value),
                );
            }
        } catch (err) {
            // silent fail by requirement
        } finally {
            setIsInferencing(false);
        }
    }

    function handleUploadFromDeviceClick() {
        setCameraMenuOpen(false);
        fileInputRef.current?.click();
    }

    function handleTakePhotoClick() {
        setCameraMenuOpen(false);

        if (canUseCamera()) {
            setShowIngredientsCamera(true);
        } else {
            cameraInputRef.current?.click();
        }
    }

    function handleUploadFileChange(e) {
        const file = e.target.files?.[0];
        if (!file) return;
        detectIngredientsFromImage(file);
        e.target.value = "";
    }

    function handleCameraFileChange(e) {
        const file = e.target.files?.[0];
        if (!file) return;
        detectIngredientsFromImage(file);
        e.target.value = "";
    }

    function handleCameraCapture(file) {
        detectIngredientsFromImage(file);
        setShowIngredientsCamera(false);
    }

    return (
        <section
            id="custom-search-recipes-page"
            className="custom-search-recipes-page"
        >
            <div className="container">
                <div className="custom-search-recipes-left">
                    <h1>
                        <span className="green-block">Custom</span>
                        <span> Search Recipes</span>
                    </h1>
                    <p>
                        The custom search feature helps users find menu options
                        that truly match their needs.
                    </p>
                </div>

                <div className="custom-search-recipes-right">
                    <form onSubmit={handleSubmit}>
                        <div className="input-group">
                            <label htmlFor="calories">Calories (±)</label>

                            <div className="input-group-identifier">
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    min="0"
                                    step="1"
                                    id="calories"
                                    value={data.calories}
                                    onChange={(e) =>
                                        setData("calories", e.target.value)
                                    }
                                    placeholder="Calories"
                                />
                                <span className="identifier">kcal</span>
                            </div>

                            {errors.calories && (
                                <small className="error-text">
                                    {errors.calories}
                                </small>
                            )}
                        </div>

                        <div className="input-group">
                            <label htmlFor="protein">Protein (±)</label>

                            <div className="input-group-identifier">
                                <input
                                    id="protein"
                                    type="number"
                                    inputMode="decimal"
                                    min="0"
                                    step="0.1"
                                    value={data.protein}
                                    onChange={(e) =>
                                        setData("protein", e.target.value)
                                    }
                                    placeholder="Protein"
                                />
                                <span className="identifier">gr</span>
                            </div>

                            {errors.protein && (
                                <small className="error-text">
                                    {errors.protein}
                                </small>
                            )}
                        </div>

                        <div className="input-group">
                            <label htmlFor="sodium">Sodium (±)</label>

                            <div className="input-group-identifier">
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    min="0"
                                    step="1"
                                    id="sodium"
                                    value={data.sodium}
                                    onChange={(e) =>
                                        setData("sodium", e.target.value)
                                    }
                                    placeholder="Sodium"
                                />
                                <span className="identifier">mg</span>
                            </div>

                            {errors.sodium && (
                                <small className="error-text">
                                    {errors.sodium}
                                </small>
                            )}
                        </div>

                        <div className="input-group">
                            <label htmlFor="fat">Fat (±)</label>

                            <div className="input-group-identifier">
                                <input
                                    type="number"
                                    inputMode="decimal"
                                    min="0"
                                    step="0.1"
                                    id="fat"
                                    value={data.fat}
                                    onChange={(e) =>
                                        setData("fat", e.target.value)
                                    }
                                    placeholder="Fat"
                                />
                                <span className="identifier">gr</span>
                            </div>

                            {errors.fat && (
                                <small className="error-text">
                                    {errors.fat}
                                </small>
                            )}
                        </div>

                        <CustomDatalist
                            label="Select Allergies"
                            options={(allergies || []).map((a) => ({
                                value: a.allergy_id,
                                label: a.allergy_name,
                            }))}
                            value={selectedAllergies}
                            onChange={handleAllergyChange}
                            placeholder="Type allergy..."
                        />

                        <CustomDatalist
                            label="Select Dietary Preferences"
                            options={(dietary_preferences || []).map((dp) => ({
                                value: dp.dietary_preference_id,
                                label: dp.diet_name,
                            }))}
                            value={selectedDietaryPreferences}
                            onChange={handleDietaryPreferencesChange}
                            placeholder="Type dietary preference..."
                        />

                        <CustomDatalist
                            useCamera={true}
                            label="Select Ingredients"
                            options={(ingredients || []).map((i) => ({
                                value: i.ingredient_id,
                                label:
                                    i.ingredient_name[0].toUpperCase() +
                                    i.ingredient_name.slice(1),
                            }))}
                            value={selectedIngredients}
                            onChange={handleIngredientsChange}
                            placeholder="Type ingredients you have..."
                            cameraMenuOpen={cameraMenuOpen}
                            onCameraClick={() =>
                                setCameraMenuOpen((prev) => !prev)
                            }
                            onCameraMenuClose={() => setCameraMenuOpen(false)}
                            cameraMenu={
                                <div
                                    className={`dropdown-menu camera-action-menu ${
                                        cameraMenuOpen ? "open" : ""
                                    }`}
                                >
                                    <div
                                        className="dropdown-item"
                                        onClick={handleTakePhotoClick}
                                    >
                                        Take a Photo
                                    </div>
                                    <div
                                        className="dropdown-item"
                                        onClick={handleUploadFromDeviceClick}
                                    >
                                        Upload from Device
                                    </div>
                                </div>
                            }
                            isLoading={isInferencing}
                        />

                        <input
                            ref={fileInputRef}
                            type="file"
                            accept="image/*"
                            hidden
                            onChange={handleUploadFileChange}
                        />

                        <input
                            ref={cameraInputRef}
                            type="file"
                            accept="image/*"
                            capture="environment"
                            hidden
                            onChange={handleCameraFileChange}
                        />

                        {showIngredientsCamera && (
                            <CameraCaptureModal
                                onCapture={handleCameraCapture}
                                onClose={() => setShowIngredientsCamera(false)}
                            />
                        )}

                        <div className="mt-1 custom-search-form-btns">
                            <button
                                type="submit"
                                className="btn btn-fill btn-full"
                                disabled={processing}
                            >
                                {processing ? "Searching..." : "Search Recipes"}
                            </button>

                            <button
                                type="button"
                                className="btn btn-line-white btn-full"
                                onClick={handleReset}
                                disabled={processing}
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    );
}

CustomSearchRecipes.layout = (page) => <Layout children={page} />;
