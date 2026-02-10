import { Link, useForm } from "@inertiajs/react";
import Layout from "../Layouts/Layout";
import { useState } from "react";

import "../../css/Authentication.css";
import FlashMessage from "../Components/FlashMessage";

export default function Login() {
    const [hidePassword, setHidePassword] = useState(true);

    const { data, setData, post, processing, errors } = useForm({
        login: "",
        password: "",
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        post("/login");
    };

    return (
        <section className="login" id="login">
            <div className="auth-left">
                <Link href="/" className="logo">
                    <img
                        src="/assets/logo/logo-transparent.png"
                        alt="Trofes Logo"
                    />
                </Link>

                <h2>Trofes</h2>
                <p className="subtitle">Selamat datang di dunia penuh gizi</p>

                <div className="wrapper">
                    <form onSubmit={handleSubmit}>
                        <FlashMessage className="mb-1" />
                        <div className="input-group">
                            <label htmlFor="login-cred">
                                Email atau Username
                            </label>
                            <input
                                type="text"
                                id="login-cred"
                                value={data.login}
                                onChange={(e) =>
                                    setData("login", e.target.value)
                                }
                                placeholder="emailanda@gmail.com"
                            />
                            {errors.login && (
                                <small className="error-text">
                                    {errors.login}
                                </small>
                            )}
                        </div>

                        <div className="input-group">
                            <label htmlFor="password">Password</label>

                            <div className="password-input">
                                <input
                                    type={hidePassword ? "password" : "text"}
                                    id="password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    placeholder="Password"
                                />
                                <span
                                    className="eye-btn"
                                    onClick={() =>
                                        setHidePassword((prev) => !prev)
                                    }
                                >
                                    {hidePassword ? (
                                        <i className="fa-solid fa-eye-slash"></i>
                                    ) : (
                                        <i className="fa-solid fa-eye"></i>
                                    )}
                                </span>
                            </div>

                            {errors.password && (
                                <small className="error-text">
                                    {errors.password}
                                </small>
                            )}
                        </div>

                        <div className="checkbox-row">
                            <div className="checkbox-mini">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    checked={data.remember}
                                    onChange={(e) =>
                                        setData("remember", e.target.checked)
                                    }
                                />
                                <label htmlFor="remember">
                                    Ingat saya 30 hari kedepan
                                </label>
                            </div>

                            <Link href="/forgot-password" className="forgot">
                                Lupa password?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            className="btn btn-fill btn-full"
                            disabled={processing}
                        >
                            {processing ? "Logging in..." : "Login"}
                        </button>

                        {/* <button type="button" className="btn btn-google btn-full">
                            <img src="/assets/icons/google.png" alt="Google" />
                            Login with Google
                        </button> */}

                        <button
                            type="button"
                            className="btn btn-google btn-full"
                            onClick={() =>
                                (window.location.href = "/auth/google/redirect")
                            }
                        >
                            <img src="/assets/icons/google.png" alt="Google" />
                            Login with Google
                        </button>

                        <p className="signup-cta">
                            Belum mempunyai akun?{" "}
                            <Link href="/sign-up">Sign Up</Link>
                        </p>
                    </form>
                </div>
            </div>

            <div className="auth-right">
                <div className="right-text">
                    <h1>
                        Buka Wawasan Gizi. <br />
                        Jadi Lebih Sehat Setiap Hari.
                    </h1>
                    <p>
                        Tingkatkan kesehatanmu melalui pemahaman gizi dan
                        pilihan makanan cerdas setiap hari.
                    </p>
                </div>
            </div>
        </section>
    );
}

Login.layout = (page) => <Layout children={page} />;
