import Layout from "../Layouts/Layout";
import { useMemo, useState } from "react";
import "../../css/GuidesPage.css";
import { router, useForm } from "@inertiajs/react";
import Paginator from "../Components/Paginator";
import Dropdown from "../Components/Dropdown";
import GuideCard from "../Components/GuideCard";

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
                    Ayo <span className="green-block">Belajar</span> Bersama
                </h1>

                <p className="guides-page-about">
                    Temukan berbagai tutorial yang telah kami siapkan untuk membantu Anda memahami setiap langkah dengan lebih mudah. Mulai dari panduan dasar hingga tips lanjutan, semuanya dirancang agar proses belajar terasa ringan, jelas, dan menyenangkan.
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

                <div className="guides-page-list">
                    {displayGuides.map((guide) => (
                        <GuideCard guide={guide}/>
                    ))}
                </div>

                <Paginator paginator={guides} onNavigate={(url) => router.get(url)} />
            </div>
        </section>
    );
}

Guides.layout = (page) => <Layout children={page} />;
