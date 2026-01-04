import Layout from '../Layouts/Layout'
import '../../css/CustomSerachRecipes.css'
import CustomDatalist from '../Components/CustomDatalist';
import { useState } from 'react';
import { useForm } from '@inertiajs/react';   

export default function CustomSearchRecipes({
    allergies, dietary_preferences, user_allergies, user_dietary_preferences, user, ingredients
}) {

    const { data, setData, put, delete: destroy, processing, errors } = useForm({
        allergies: user_allergies || [],
        dietary_preferences: user_dietary_preferences || [],
        ingredients: [],
        calories: '',
        protein: '',
        fat: '',
        sodium: ''
    })

    const [selectedAllergies, setSelectedAllergies] = useState(
        user_allergies.map(a => ({
            value: a,
            label: allergies.find(allergy => allergy.allergy_id === a)?.allergy_name || '',
        })) || []
    )

    const [selectedDietaryPreferences, setSelectedDietaryPreferences] = useState(
        user_dietary_preferences.map(d => ({
            value: d,
            label: dietary_preferences.find(dp => dp.dietary_preference_id === d)?.diet_name || '',
        })) || []
    )

    const [selectedIngredients, setSelectedIngredients] = useState([])

    function handleIngredientsChange(newValues) {
        setSelectedIngredients(newValues)
        setData(
            'ingredients',
            newValues.map(v => v.value)
        )
    }

    function handleDietaryPreferencesChange(newValues) {
        setSelectedDietaryPreferences(newValues)
        setData(
            'dietary_preferences',
            newValues.map(v => v.value)
        )
    }

    function handleAllergyChange(newValues) {
        setSelectedAllergies(newValues)
        setData(
            'allergies',
            newValues.map(v => v.value)
        )
    }

    return (
        <section id="custom-search-recipes-page" className="custom-search-recipes-page">
            <div className="container">
                <div className="custom-search-recipes-left">
                    <h1>
                        <span className="green-block">Custom</span>
                        <span>
                            Search Recipes
                        </span>
                    </h1>
                    <p>
                        The custom search feature helps users find menu options that truly match their needs. Users can filter food by nutritional values such as protein or calories, adjust for allergies and dietary preferences, and choose menus that fit the ingredients they already have. As a result, the search becomes more personalized, faster, and more relevant.
                    </p>
                </div>

                <div className="custom-search-recipes-right">
                    <form action="">
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
                                    onChange={(e) => setData('calories', e.target.value)}
                                    placeholder="Calories"
                                />
                                <span
                                    className="identifier"
                                >
                                    kcal
                                </span>
                            </div>

                            {errors.calories && (
                                <small className="error-text">{errors.calories}</small>
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
                                    onChange={(e) => setData('protein', e.target.value)}
                                    placeholder="Protein"
                                />
                                <span
                                    className="identifier"
                                >
                                    gr
                                </span>
                            </div>

                            {errors.protein && (
                                <small className="error-text">{errors.protein}</small>
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
                                    onChange={(e) => setData('sodium', e.target.value)}
                                    placeholder="Sodium"
                                />
                                <span
                                    className="identifier"
                                >
                                    mg
                                </span>
                            </div>

                            {errors.sodium && (
                                <small className="error-text">{errors.sodium}</small>
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
                                    onChange={(e) => setData('fat', e.target.value)}
                                    placeholder="Fat"
                                />
                                <span
                                    className="identifier"
                                >
                                    gr
                                </span>
                            </div>

                            {errors.fat && (
                                <small className="error-text">{errors.fat}</small>
                            )}
                        </div>

                        <CustomDatalist
                            label="Select Allergies"
                            options={allergies.map(a => ({
                                value: a.allergy_id,
                                label: a.allergy_name,
                            }))}
                            value={selectedAllergies}
                            onChange={handleAllergyChange}
                            placeholder="Type allergy..."
                        />
            
                        <CustomDatalist
                            label="Select Dietary Preferences"
                            options={dietary_preferences.map(a => ({
                                value: a.dietary_preference_id,
                                label: a.diet_name,
                            }))}
                            value={selectedDietaryPreferences}
                            onChange={handleDietaryPreferencesChange}
                            placeholder="Type dietary preference..."
                        />

                        <CustomDatalist
                            label="Select Ingredients"
                            options={ingredients.map(a => ({
                                value: a.ingredient_id,
                                label: a.ingredient_name[0].toUpperCase() + a.ingredient_name.slice(1),
                            }))}
                            value={selectedIngredients}
                            onChange={handleIngredientsChange}
                            placeholder="Type ingredients you have..."
                        />

                        <button type="submit" className="btn btn-fill btn-full mt-1">Search Recipes</button>
                    </form>
                </div>
            </div>
        </section>
    )
}

CustomSearchRecipes.layout = page => <Layout children={page} />