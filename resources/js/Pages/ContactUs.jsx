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
                            Ayo <span className="green-block">tumbuh</span>
                        </div>
                        <div className="hero-title-bottom">
                            <span className="hide">Ayo</span> bersama.
                        </div>
                    </h1>

                    <div className="contact-layout">
                        <div className="contact-info">
                            <p>
                                Jika Anda ingin berdiskusi lebih lanjut atau
                                memiliki pertanyaan, jangan ragu untuk
                                mengirimkan pesan ke kami. Kami siap membantu
                                Anda memulai perjalanan menuju pilihan makan
                                yang lebih cerdas. Kami terbuka dari jam 08.00 -
                                18.00, hari Sen - Jum.
                            </p>

                            <div className="info-block">
                                <p>
                                    <strong>Hubungi kami:</strong> +62
                                    898946353003
                                </p>
                                <p>
                                    <strong>Sapa kami:</strong>{" "}
                                    sevendeadlysins@gmail.com
                                </p>
                                <p>
                                    <strong>Alamat:</strong> Sentul City, Jl.
                                    Pakuan No.3, Sumur Batu, Babakan Madang,
                                    Bogor Regency, West Java 16810
                                </p>
                            </div>

                            <div className="social-icons">
                                <a href="#">
                                    <i className="fab fa-facebook-f"></i>
                                </a>
                                <a href="#">
                                    <i className="fab fa-linkedin-in"></i>
                                </a>
                                <a href="#">
                                    <i className="fab fa-instagram"></i>
                                </a>
                                <a href="#">
                                    <i className="fab fa-youtube"></i>
                                </a>
                            </div>
                        </div>

                        <div className="contact-form">
                            <form id="contactForm" onSubmit={handleSubmit}>
                                <div className="form-row">
                                    <div className="input-group">
                                        <label for="name">Nama</label>
                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            placeholder="seven deadly sins"
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
                                            placeholder="emailanda@gmail.com"
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
                                    <label for="message">Pesan</label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        placeholder="Pesan Anda"
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
                                    {
                                        processing ? "Mengirim..." : "Kirim Pesan"
                                    }
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
