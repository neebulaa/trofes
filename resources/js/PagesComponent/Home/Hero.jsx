import HeroCarausel from "../../Components/HeroCarausel"
import { Link } from "@inertiajs/react"

export default function Hero({recipes}) {
    return (
        <section id="hero" className="hero">
            <div className="hero-inner container">
                <div className="hero-badge">
                    <img src="/assets/graphics/fireworks.png" alt="fireworks-graphics" />
                    <p>Selamat datang di Trofes</p>
                </div>

                <h1 className="hero-title">
                    Teman andalanmu di <span className="green-block">dapur</span> untuk setiap hidangan
                </h1>

                <p className="hero-sub">
                    Misi kami adalah menjadi teman andalanmu di dapur, membantu kamu menyiapkan hidangan yang lebih mudah, lebih cerdas, dan lebih lezat sesuai dengan kebutuhanmu setiap hari
                </p>

                <div className="hero-cta">
                    <Link className="btn btn-fill" href="/recipes">Coba Sekarang</Link>
                </div>

            </div>

            <HeroCarausel recipes={recipes} />
        </section>
    )
}
