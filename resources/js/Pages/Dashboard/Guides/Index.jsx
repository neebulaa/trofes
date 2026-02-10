import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import NotFoundSection from "../../../Components/NotFoundSection";
import Paginator from "../../../Components/Paginator";
import { useForm } from "@inertiajs/react";
import FlashMessage from "../../../Components/FlashMessage";
import { router } from "@inertiajs/react";

export default function Guides({ guides }) {
    const { delete: destroy } = useForm();
    const { data, setData, get, errors } = useForm({
        search: "",
    });

    function deleteGuide(e, guide_slug) {
        e.preventDefault();
        if (confirm("Are you sure you want to delete this guide?")) {
            destroy(`/dashboard/guides/${guide_slug}`, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        get("/dashboard/guides", {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            data: { search: data.search },
        });
    }

    return (
        <DashboardLayout title="Dashboard - Guides">
            <div className="crud-header space-between">
                <div>
                    <h1 className="crud-title">Guides</h1>
                    <p className="text-muted">
                        Manage published nutrition guides.
                    </p>
                </div>

                <Link
                    href="/dashboard/guides/create"
                    className="btn btn-fill btn-sm"
                >
                    <i className="fa-solid fa-plus"></i>
                    <span className="ml-05">Add Guide</span>
                </Link>
            </div>

            <FlashMessage className="mt-1" />

            <form onSubmit={handleSubmit}>
                <div className="search-input mt-1">
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
            </form>

            {guides.data.length === 0 && (
                <NotFoundSection
                    className="mt-1"
                    message="No Guides Found"
                    description="There is no guides in the system."
                />
            )}

            {guides.data.length > 0 && (
                <div className="crud-card-table mt-1">
                    <table className="crud-table">
                        <thead>
                            <tr>
                                <th className="no-min-width">Image</th>
                                <th>Title</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            {guides.data.map((guide) => (
                                <tr key={guide.guide_id}>
                                    <td className="no-min-width">
                                        <div className="crud-image crud-image-wide">
                                            <img
                                                src={guide.public_image}
                                                alt={guide.title}
                                                loading="lazy"
                                            />
                                        </div>
                                    </td>

                                    <td>
                                        <h4 className="crud-table-title">
                                            {guide.title}
                                        </h4>
                                        <div className="crud-sub">
                                            {guide.slug}
                                        </div>
                                    </td>

                                    <td>
                                        {new Date(
                                            guide.published_at,
                                        ).toLocaleDateString()}
                                    </td>

                                    <td className="text-right">
                                        <div className="crud-actions">
                                            <Link
                                                href={`/dashboard/guides/${guide.slug}`}
                                                className="icon-btn btn-info-outline"
                                                title="View guide"
                                                aria-label="View guide"
                                            >
                                                <i className="fa-regular fa-eye" />
                                            </Link>

                                            <Link
                                                href={`/dashboard/guides/${guide.slug}/edit`}
                                                className="icon-btn btn-secondary-outline"
                                                title="Edit guide"
                                                aria-label="Edit guide"
                                            >
                                                <i className="fa-regular fa-pen-to-square" />
                                            </Link>

                                            <form
                                                action=""
                                                onSubmit={(e) =>
                                                    deleteGuide(e, guide.slug)
                                                }
                                            >
                                                <button
                                                    className="icon-btn btn-danger-outline"
                                                    title="Delete guide"
                                                    aria-label="Delete guide"
                                                >
                                                    <i className="fa-regular fa-trash-can" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <Paginator
                paginator={guides}
                onNavigate={(url) => router.get(url)}
            />
        </DashboardLayout>
    );
}
