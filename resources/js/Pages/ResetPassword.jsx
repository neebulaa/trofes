import '../../css/ForgotAndResetPassword.css';
import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import Layout from '../Layouts/Layout';

export default function ResetPassword({ token, email, username }) {
    const [hidePassword, setHidePassword] = useState(true);
    const [hideConfirmPassword, setHideConfirmPassword] = useState(true);
    const { data, setData, post, processing, errors } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/reset-password');
    }

    return (
        <section id="reset-password">
            <div className="container">
                <div className="logo">
                    <img src="/assets/logo/logo-transparent.png" alt="Logo" />
                </div>

                <h1 className="title">Change password for {username}</h1>
                <p className="subtitle">
                    Make sure it must be at least 15 characters OR at least 8 characters including a number and a lowercase letter.
                </p>

                <form onSubmit={handleSubmit}>
                    <div className="input-group">
                        <label htmlFor="password">Password</label>
                        <div className="password-input">
                            <input
                                id="password"
                                type={hidePassword ? 'password' : 'text'}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Password"
                            />
                            <span
                                className="eye-btn"
                                onClick={() => setHidePassword(p => !p)}
                            >
                                {hidePassword
                                    ? <i className="fa-solid fa-eye-slash"></i>
                                    : <i className="fa-solid fa-eye"></i>
                                }
                            </span>
                        </div>
                        {errors.password && (
                            <small className="error-text">{errors.password}</small>
                        )}
                    </div>

                    <div className="input-group">
                        <label htmlFor="password_confirmation">
                            Confirmation Password
                        </label>
                        <div className="password-input">
                            <input
                                id="password_confirmation"
                                type={hideConfirmPassword ? 'password' : 'text'}
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData('password_confirmation', e.target.value)
                                }
                                placeholder="Confirm Password"
                            />
                            <span
                                className="eye-btn"
                                onClick={() => setHideConfirmPassword(p => !p)}
                            >
                                {hideConfirmPassword
                                    ? <i className="fa-solid fa-eye-slash"></i>
                                    : <i className="fa-solid fa-eye"></i>
                                }
                            </span>
                        </div>
                        {errors.password_confirmation && (
                            <small className="error-text">
                                {errors.password_confirmation}
                            </small>
                        )}
                    </div>

                    <button
                        type="submit"
                        className="btn btn-fill mt-1"
                        disabled={processing}
                    >
                        {processing ? "Changing..." : "Change Password"}
                    </button>
                </form>
            </div>
        </section>
    );
}

ResetPassword.layout = page => <Layout children={page} />
