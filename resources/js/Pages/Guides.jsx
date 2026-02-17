import Layout from "../Layouts/Layout";
import { useMemo, useState, useEffect, useCallback } from "react";
import "../../css/GuidesPage.css";
import { router, useForm, usePage } from "@inertiajs/react";
import Paginator from "../Components/Paginator";
import Dropdown from "../Components/Dropdown";
import GuideCard from "../Components/GuideCard";
import NotFoundSection from "../Components/NotFoundSection";

export default function Guides({ guides, random_guides }) {
    const { url } = usePage();

    const { data, setData, errors } = useForm({
        search: "",
    });

    const categoryOptions = useMemo(
        () => [
            { label: "Latest", value: "latest" },
            { label: "Oldest", value: "oldest" },
            { label: "A - Z", value: "alphabetical" },
            { label: "Z - A", value: "reverse-alphabetical" },
        ],
        [],
    );

    const [category, setCategory] = useState(categoryOptions[0]);

    // Merge URL query params instead of replacing them
    const navigateWithMergedQuery = useCallback(
        (updates) => {
            const u = new URL(url, window.location.origin);
            const params = Object.fromEntries(u.searchParams.entries());

            Object.entries(updates || {}).forEach(([key, value]) => {
                if (value === undefined || value === null || value === "") {
                    delete params[key];
                } else {
                    params[key] = String(value);
                }
            });

            router.get("/guides", params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [url],
    );

    // Sync search input with URL
    useEffect(() => {
        const u = new URL(url, window.location.origin);
        const q = u.searchParams.get("search") ?? "";
        setData((prev) => ({ ...prev, search: q }));
    }, [url, setData]);

    // Sync dropdown with URL (?sort=...)
    useEffect(() => {
        const u = new URL(url, window.location.origin);
        const sort = u.searchParams.get("sort") ?? "latest"; // default latest
        const found =
            categoryOptions.find((o) => o.value === sort) ?? categoryOptions[0];
        setCategory(found);
    }, [url, categoryOptions]);

    function handleSubmit(e) {
        e.preventDefault();
        navigateWithMergedQuery({
            search: data.search,
            page: undefined, // reset page on search
        });
    }

    // Backend-sorted guides (no frontend sorting)
    const displayGuides = useMemo(() => {
        return Array.isArray(guides?.data) ? guides.data : [];
    }, [guides]);

    return (
        <section className="guides-page" id="guides-page">
            <div className="container guides-container">
                <h1 className="guides-page-title">
                    Lets <span className="green-block">Study</span> Together
                </h1>

                <p className="guides-page-about">
                    Discover various tutorials prepared to assist you in
                    understanding each step more effectively.
                </p>

                <div className="search-and-filters">
                    <form onSubmit={handleSubmit}>
                        <div className="filters">
                            <p className="filters-text">Filter by:</p>
                            <Dropdown
                                options={categoryOptions}
                                value={category}
                                onChange={(next) => {
                                    setCategory(next);
                                    navigateWithMergedQuery({
                                        sort: next.value,
                                        page: undefined,
                                    });
                                }}
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
                                    onChange={(e) =>
                                        setData("search", e.target.value)
                                    }
                                    placeholder="Search guides..."
                                />

                                <button type="submit" className="search-btn">
                                    Search
                                </button>
                            </div>

                            {errors.search && (
                                <small className="error-text">
                                    {errors.search}
                                </small>
                            )}
                        </div>
                    </form>
                </div>

                {displayGuides.length === 0 ? (
                    <>
                        <NotFoundSection message="No guides found." />
                        <h2 className="mt-2">Other Guides</h2>
                        <div className="guides-page-list" style={{ marginTop: '1rem' }}>
                            {random_guides.map((guide) => (
                                <GuideCard guide={guide} key={guide.guide_id} />
                            ))}
                        </div>
                    </>
                ) : (
                    <div className="guides-page-list">
                        {displayGuides.map((guide) => (
                            <GuideCard guide={guide} key={guide.guide_id} />
                        ))}
                    </div>
                )}

                <Paginator
                    paginator={guides}
                    onNavigate={(to) =>
                        router.get(
                            to,
                            {},
                            { preserveState: true },
                        )
                    }
                />
            </div>
        </section>
    );
}

Guides.layout = (page) => <Layout children={page} />;