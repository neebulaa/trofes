export default function About(){
    return (
    <section className="about" id="about">
        <div className="container">
            <img
                src="/assets/graphics/wind-abst.png"
                alt="graphic"
                className="about-scribble"
            />
            <h2 className="about-title">
                Personalized Smart Food Recommendations to Simplify Your Daily Prep and Make Every Meal Easier
            </h2>


            <div className="about-bottom">
                <div className="stats">
                    <div className="stat">
                        <div className="label">Number of Recipes</div>
                        <div className="value">4K+</div>
                    </div>
                    <div className="stat">
                        <div className="label">Amount of ingredients Recognized</div>
                        <div className="value">100+</div>
                    </div>
                </div>

                <div className="about-cta">
                    <p>
                    Trofes is an intelligent nutrition partner designed to deliver food recommendations that fit your lifestyle and goals. We empower you to make healthier decisions through simple, actionable insights that take the guesswork out of every meal.
                    </p>

                    <a className="btn btn-line" href="#hubungi">
                        Contact us now
                    </a>
                </div>
            </div>
        </div>
    </section>
    )
}