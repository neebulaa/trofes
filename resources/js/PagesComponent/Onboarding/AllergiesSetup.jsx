import { useForm } from '@inertiajs/react'

export default function AllergiesSetup({ allergies, handleNextScreen, user_allergies }) {
    const { data, setData, post, processing, errors } = useForm({
        allergies: user_allergies || [],
    })

    const toggleAllergy = (id) => {
        setData(
            'allergies',
            data.allergies.includes(id)
                ? data.allergies.filter(item => item !== id)
                : [...data.allergies, id]
        )
    }

    const handleSubmit = (e) => {
        e.preventDefault()

        post('/onboarding/allergies-setup', {
            onSuccess: () => {
                handleNextScreen()
            },
        })
    }

    return (
        <form onSubmit={handleSubmit}>
            <div className="allergy-grid">
                {allergies.map((allergy) => {
                    const selected = data.allergies.includes(allergy.allergy_id)

                    return (
                        <div
                            key={allergy.allergy_id}
                            className={`card ${selected ? 'selected' : ''}`}
                            onClick={() => toggleAllergy(allergy.allergy_id)}
                        >
                            <input
                                type="checkbox"
                                hidden
                                checked={selected}
                                onChange={() => {}}
                            />

                            <div className="icon-box">
                                <img
                                    src={allergy.public_image}
                                    alt={allergy.allergy_name}
                                />
                            </div>

                            <div className="card-title">
                                {allergy.allergy_name}
                            </div>

                            <div className="card-desc">
                                {allergy.examples}
                            </div>
                        </div>
                    )
                })}
            </div>

            {errors.allergies && (
                <small className="error-text">{errors.allergies}</small>
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