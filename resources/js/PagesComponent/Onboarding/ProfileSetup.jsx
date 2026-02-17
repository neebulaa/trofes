import { useForm } from '@inertiajs/react'
import Layout from '../../Layouts/Layout'
import { useRef } from 'react'

export default function ProfileSetup({ user, handleNextScreen }) {
    const { data, setData, post, processing, errors } = useForm({
        full_name: user?.full_name || '',
        phone: user?.phone || '',
        gender: user?.gender || '',
        birth_date: user?.birth_date || '',
    })

    const handleSubmit = (e) => {
        e.preventDefault()
        post('/onboarding/profile-setup', {
            onSuccess: () => {
                handleNextScreen()
            },
        })
    }

    return (
        <form onSubmit={handleSubmit} className="profile-setup-form">
            <div className="input-group">
                <label htmlFor="full_name">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    value={data.full_name}
                    onChange={(e) => setData('full_name', e.target.value)}
                    placeholder="Seven Sins"
                />
                {errors.full_name && (
                    <small className="error-text">{errors.full_name}</small>
                )}
            </div>

            <div className="input-group">
                <label htmlFor="phone">Phone Number</label>
                <div className="nohp-input">
                    <div className="phone-identifier">+62</div>
                    <input
                        type="tel"
                        id="phone"
                        value={data.phone}
                        onChange={(e) => setData('phone', e.target.value)}
                        placeholder="81234567890"
                    />
                </div>
                {errors.phone && (
                    <small className="error-text">{errors.phone}</small>
                )}
            </div>

            <div className="input-group">
                <label htmlFor="gender">Gender</label>
                <select
                    id="gender"
                    value={data.gender}
                    onChange={(e) => setData('gender', e.target.value)}
                >
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="silent">Prefer not to say</option>
                </select>
                {errors.gender && (
                    <small className="error-text">{errors.gender}</small>
                )}
            </div>

            <div className="input-group">
                <label htmlFor="birth_date">Date of Birth</label>
                <input
                    type="date"
                    id="birth_date"
                    value={data.birth_date}
                    onChange={(e) =>
                        setData('birth_date', e.target.value)
                    }
                />
                {errors.birth_date && (
                    <small className="error-text">
                        {errors.birth_date}
                    </small>
                )}
            </div>

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
            disabled={processing}>
                Skip
            </button>
        </form>
    )
}

ProfileSetup.layout = page => <Layout children={page} />
