import HeroCarausel from "../../Components/HeroCarausel"
import { Link } from "@inertiajs/react"

export default function Hero({recipes}) {
    return (
        <section id="hero" className="hero">
            <div className="hero-inner container">
                <div className="hero-badge">
                    <img src="/assets/graphics/fireworks.png" alt="fireworks-graphics" />
                    <p>Less kitchen stress, more mealtime success.</p>
                </div>

                <h1 className="hero-title">
                    Your trusted <span className="green-block">kitchen</span> ally for every single meal
                </h1>

                <p className="hero-sub">
                    Our mission is to empower your cooking journey, making meal preparation easier, more varied, and more delicious to meet your personal needs every day.
                </p>

                <div className="hero-cta">
                    <Link className="btn btn-fill" href="/recipes">Explore recipes</Link>
                </div>

            </div>

            <HeroCarausel recipes={recipes} />
        </section>
    )
}
