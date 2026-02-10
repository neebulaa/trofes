import Layout from "../Layouts/Layout";
import { useMemo, useState } from "react";
import "../../css/GuidesPage.css";
import { router, useForm } from "@inertiajs/react";
import Paginator from "../Components/Paginator";
import Dropdown from "../Components/Dropdown";
import GuideCard from "../Components/GuideCard";
import NotFoundSection from "../Components/NotFoundSection";

export default function Guides({ guides, filters }) {
    const { data, setData, get, errors } = useForm({
        search: "",
    });

    const categoryOptions = [
        { label: "Latest", value: "latest" },
        { label: "Oldest", value: "oldest" },
        { label: "A - Z", value: "alphabetical" },
        { label: "Z - A", value: "reverse-alphabetical" },
    ];

    const [category, setCategory] = useState(categoryOptions[0]);

    function handleSubmit(e) {
        e.preventDefault();
        get("/guides", {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            data: { search: data.search },
        });
    }

    const displayGuides = useMemo(() => {
        const items = Array.isArray(guides?.data) ? [...guides.data] : [];

        switch (category.value) {
            case "latest":
                return items.sort(
                    (a, b) => new Date(b.published_at) - new Date(a.published_at)
                );

            case "oldest":
                return items.sort(
                    (a, b) => new Date(a.published_at) - new Date(b.published_at)
                );

            case "alphabetical":
                return items.sort((a, b) =>
                    String(a.title).localeCompare(String(b.title), "id", {
                        sensitivity: "base",
                    })
                );

            case "reverse-alphabetical":
                return items.sort((a, b) =>
                    String(b.title).localeCompare(String(a.title), "id", {
                        sensitivity: "base",
                    })
                );

            default:
                return items;
        }
    }, [guides, category]);

    return (
        <section className="guides-page" id="guides-page">
            <div className="container guides-container">
                <h1 className="guides-page-title">
                    Lets <span className="green-block">Study</span> Together
                </h1>

                <p className="guides-page-about">
                    Discover various tutorials prepared to assist you in understanding each step more effectively.
                </p>

                <div className="search-and-filters">
                    <form onSubmit={handleSubmit}>
                        <div className="filters">
                            <p className="filters-text">Filter by:</p>
                            <Dropdown
                                options={categoryOptions}
                                value={category}
                                onChange={setCategory}
                            />
                        </div>

                        <div className="input-group">
                            <div className="search-input">
                                <span>
                                    <i className="fa-solid fa-magnifying-glass"></i>
                                </span>

                                <input
                                    type="text"
                                    value={data.search}
                                    onChange={(e) => setData("search", e.target.value)}
                                    placeholder="Search guides..."
                                />

                                <button type="submit" className="search-btn">
                                    Search
                                </button>
                            </div>

                            {errors.search && <small className="error-text">{errors.search}</small>}
                        </div>
                    </form>
                </div>

                {
                    displayGuides.length === 0 ? (
                        <NotFoundSection message="No guides found." />
                    ) : 
                    <div className="guides-page-list">
                        {displayGuides.map((guide) => (
                            <GuideCard guide={guide}/>
                        ))}
                    </div>
                }

                <Paginator paginator={guides} onNavigate={(url) => router.get(url)} />
            </div>
        </section>
    );
}

Guides.layout = (page) => <Layout children={page} />;
