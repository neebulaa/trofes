import { useForm } from '@inertiajs/react'

export default function DietaryPreferencesSetup({ dietary_preferences, handleNextScreen, user_dietary_preferences }) {
    const { data, setData, post, processing, errors } = useForm({
        preferences: user_dietary_preferences || [],
    })

    const togglePreference = (id) => {
        setData(
            'preferences',
            data.preferences.includes(id)
                ? data.preferences.filter(item => item !== id)
                : [...data.preferences, id]
        )
    }

    const handleSubmit = (e) => {
        e.preventDefault()

        post('/onboarding/dietary-preferences-setup', {
            onSuccess: () => {
                handleNextScreen()
            },
        })
    }

    return (
        <form onSubmit={handleSubmit}>
            <div className="pref-grid">
                {dietary_preferences.map((preference) => {
                    const selected = data.preferences.includes(preference.dietary_preference_id)

                    return (
                        <div
                            key={preference.dietary_preference_id}
                            className={`pref-item ${selected ? 'selected' : ''}`}
                            onClick={() => togglePreference(preference.dietary_preference_id)}
                        >
                            <input
                                type="checkbox"
                                hidden
                                checked={selected}
                                onChange={() => {}}
                            />

                            <div className="icon-box">
                                <img
                                    src={preference.public_image}
                                    alt={preference.diet_name}
                                />
                            </div>

                            <p>{preference.diet_name}</p>
                        </div>
                    )
                })}
            </div>

            {errors.preferences && (
                <small className="error-text">{errors.preferences}</small>
            )}

            <button
                type="submit"
                className="mt-2 btn btn-fill btn-full"
                disabled={processing}
            >
                {processing ? 'Saving...' : 'Continue'}
            </button>

            <button
                type="button"
                className="btn btn-google btn-full"
                onClick={handleNextScreen}
                disabled={processing}
            >
                Skip
            </button>
        </form>
    )
}