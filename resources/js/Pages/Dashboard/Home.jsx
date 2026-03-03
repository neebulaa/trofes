import DashboardLayout from "../../Layouts/DashboardLayout";
import "../../../css/Dashboard/DashboardHome.css";
import RecipeCard from "../../Components/RecipeCard";
import { Link } from "@inertiajs/react";

export default function Home({
    recipe_count,
    user_count,
    ingredient_count,
    popular_recipes,
    latest_activity,
    latest_guides,
    umamiShareUrl
}) {
    const stats = [
        { label: "Total Recipes", value: recipe_count },
        { label: "Total Ingredients", value: ingredient_count },
        { label: "Active Users", value: user_count },
    ];

    return (
        <DashboardLayout title="Dashboard - Home">
            <div className="dash-home">
                <div className="dash-home__header">
                    <h1 className="dash-home__title">Dashboard Home</h1>
                    <p className="dash-home__subtitle">
                        Here's a quick overview of trofes.
                    </p>
                </div>

                <section className="dash-home__stats">
                    {stats.map((stat) => (
                        <div key={stat.label} className="dash-home__stat">
                            <p className="dash-home__stat-label">
                                {stat.label}
                            </p>
                            <p className="dash-home__stat-value">
                                {stat.value}
                            </p>
                        </div>
                    ))}
                </section>

                <section className="dash-home__card">
                    <div className="dash-home__section-head">
                        <h2 className="dash-home__section-title">Analytics</h2>
                    </div>

                    <div className="dash-home__chart">
                        <iframe
                            src={umamiShareUrl}
                            title="Umami Analytics"
                            loading="lazy"
                            width="100%"
                            height="360"
                            frameBorder="0"
                        />
                    </div>
                </section>

                <section className="dash-home__card">
                    <div className="dash-home__section-head">
                        <h2 className="dash-home__section-title">
                            Top 3 Popular Recipes
                        </h2>
                    </div>

                    <div className="dash-home__meals">
                        {popular_recipes.map((recipe) => (
                            <article
                                key={recipe.title}
                                className="dash-home__meal"
                            >
                                <RecipeCard recipe={recipe} />
                            </article>
                        ))}
                    </div>
                </section>

                <section className="dash-home__split">
                    <div className="dash-home__card">
                        <div className="dash-home__section-head">
                            <h2 className="dash-home__section-title">
                                Recent Activity
                            </h2>
                        </div>
                        <ul className="dash-home__activity">
                            {latest_activity.map((item, index) => (
                                <li
                                    key={`${item}-${index}`}
                                    className="dash-home__activity-item"
                                >
                                    {item}
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="dash-home__card">
                        <div className="dash-home__section-head">
                            <h2 className="dash-home__section-title">
                                Quick Actions
                            </h2>
                        </div>
                        <div className="dash-home__actions">
                            <Link href="/dashboard/roles" className="btn btn-fill btn-sm dash-home__action-btn">
                                Change User Role
                            </Link>
                            <Link href="/dashboard/dietary-preferences/create" className="btn btn-line btn-sm dash-home__action-btn">
                                Add New Dietary Preference
                            </Link>
                            <Link href="/dashboard/allergies" className="btn btn-line btn-sm dash-home__action-btn">
                                View all Allergies
                            </Link>
                            <Link href="/dashboard/messages" className="btn btn-line btn-sm dash-home__action-btn">
                                View User Message
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </DashboardLayout>
    );
}
