import Layout from "../Layouts/Layout";
import { useForm } from "@inertiajs/react";
import FlashMessage from "../Components/FlashMessage";
import "../../css/ContactUs.css";

export default function ContactUs() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
        email: "",
        message: "",
    });

    function handleSubmit(e) {
        e.preventDefault();
        post("/contact-us", {
            onSuccess: () => reset(),
        });
    }

    return (
        <>
            <FlashMessage className="mb-1 flash-screen" />
            <section className="contact-us" id="contact-us">
                <div className="container">
                    <h1 className="hero-title">
                        <div className="hero-title-top">
                            Let's <span className="green-block">Grow</span>
                        </div>
                        <div className="hero-title-bottom">
                            <span className="hide">Ayo</span> Together.
                        </div>
                    </h1>

                    <div className="contact-layout">
                        <div className="contact-info">
                            <p>
                                If you’d like to discuss more or have any
                                questions, feel free to send us a message. We’re
                                here to help you start your journey to smarter
                                eating. Our hours are 8:00 AM – 6:00 PM, Monday
                                through Friday.
                            </p>

                            <div className="info-block">
                                <p>
                                    <strong>Contact Us:</strong> +62
                                    898946353003
                                </p>
                                <p>
                                    <strong>Get in Touch:</strong>{" "}
                                    sevendeadlysins@gmail.com
                                </p>
                                <p>
                                    <strong>Address:</strong> Sentul City, Jl.
                                    Pakuan No.3, Sumur Batu, Babakan Madang,
                                    Bogor Regency, West Java 16810
                                </p>
                            </div>

                            <div className="social-icons">
                                <a
                                    href="https://www.facebook.com/"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <i className="fab fa-facebook-f"></i>
                                </a>
                                <a
                                    href="https://www.linkedin.com/"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <i className="fab fa-linkedin-in"></i>
                                </a>
                                <a
                                    href="https://www.instagram.com/pptibca.22/"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <i className="fab fa-instagram"></i>
                                </a>
                                <a
                                    href="https://www.youtube.com/"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <i className="fab fa-youtube"></i>
                                </a>
                            </div>
                        </div>

                        <div className="contact-form">
                            <form id="contactForm" onSubmit={handleSubmit}>
                                <div className="form-row">
                                    <div className="input-group">
                                        <label for="name">Name</label>
                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            placeholder="Full Name"
                                            required
                                            value={data.name}
                                            onChange={(e) =>
                                                setData("name", e.target.value)
                                            }
                                        />
                                        {errors.name && (
                                            <small className="error-text">
                                                {errors.name}
                                            </small>
                                        )}
                                    </div>
                                    <div className="input-group">
                                        <label for="email">Email</label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            placeholder="youremail@gmail.com"
                                            required
                                            value={data.email}
                                            onChange={(e) =>
                                                setData("email", e.target.value)
                                            }
                                        />
                                        {errors.email && (
                                            <small className="error-text">
                                                {errors.email}
                                            </small>
                                        )}
                                    </div>
                                </div>

                                <div className="input-group">
                                    <label for="message">Message</label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        placeholder="Write your message here..."
                                        required
                                        value={data.message}
                                        onChange={(e) =>
                                            setData("message", e.target.value)
                                        }
                                    ></textarea>
                                    {errors.message && (
                                        <small className="error-text">
                                            {errors.message}
                                        </small>
                                    )}
                                </div>

                                <button
                                    type="submit"
                                    className="btn-submit btn btn-fill"
                                >
                                    {processing ? "Sending..." : "Send"}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

ContactUs.layout = (page) => <Layout children={page} />;
