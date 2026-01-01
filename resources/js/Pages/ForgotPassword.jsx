import Layout from "../Layouts/Layout";
import { useState } from "react";
import { useForm } from "@inertiajs/react";
import { Link } from "@inertiajs/react";
import FlashMessage from "../Components/FlashMessage";
// import { Turnstile } from "react-turnstile";

import '../../css/ForgotAndResetPassword.css';

export default function ForgotPassword({turnstileSiteKey}){
    const [successSent, setSuccessSent] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        email: "",
        // turnstile_token: "",
    });

    function handleSubmit(e){
        e.preventDefault();
        post('/forgot-password', {
            onSuccess: () => setSuccessSent(true),
        });
    }

    return (
        <>
            <section id="forgot-password">
                <div className="container">
                    <FlashMessage className="mb-1"/>
                    <div className="logo">
                        <img src="/assets/logo/logo-transparent.png" alt="Logo" />
                    </div>
                    <h1 className="title">{successSent ? "Check your email" : "Reset your password"}</h1>
                    <p className="subtitle">
                        {successSent ? "Check your email for a link to reset your password. If you don't see it, please check your spam folder." : "Enter your user account's verified email address and we will send you a password reset link."}
                    </p>


                    {!successSent ? (
                        <form onSubmit={handleSubmit}>
                            <div className="input-group">
                                <label htmlFor="email">Email</label>
                                <input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData("email", e.target.value)}
                                placeholder="emailanda@gmail.com"
                                />
                                {errors.email && <small className="error-text">{errors.email}</small>}
                            </div>

                            {/* Turnstile Cloudfare */}
                            {/* <div className="input-group">
                                <Turnstile
                                siteKey={turnstileSiteKey}
                                onSuccess={(token) => setData("turnstile_token", token)}
                                onExpire={() => setData("turnstile_token", "")}
                                onError={() => setData("turnstile_token", "")}
                                />
                                {errors.turnstile_token && (
                                <small className="error-text">{errors.turnstile_token}</small>
                                )}
                            </div> */}

                            <button
                                type="submit"
                                className="btn btn-fill mt-1"
                                // disabled={processing || !data.turnstile_token}
                                disabled={processing}
                            >
                                {processing ? "Sending..." : "Send password reset link"}
                            </button>
                        </form>
                    ) : (
                        <Link href="/login" className="return-to-login-link">Return to Login</Link> 
                    )}
                </div>
            </section>
        </>
    );
}

ForgotPassword.layout = page => <Layout children={page} />