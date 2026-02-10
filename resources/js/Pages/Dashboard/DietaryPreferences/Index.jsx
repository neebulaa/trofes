import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import NotFoundSection from "../../../Components/NotFoundSection";
import Paginator from "../../../Components/Paginator";
import { useForm } from "@inertiajs/react";
import FlashMessage from "../../../Components/FlashMessage";
import { router } from "@inertiajs/react";

export default function DietaryPreferences({ dietary_preferences }) {
    const { delete: destroy } = useForm();
    const { data, setData, get, errors } = useForm({
        search: "",
    });

    function deleteDietaryPreference(e, diet_code) {
        e.preventDefault();
        if (
            confirm("Are you sure you want to delete this dietary preference?")
        ) {
            destroy(`/dashboard/dietary-preferences/${diet_code}`, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        get("/dashboard/dietary-preferences", {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            data: { search: data.search },
        });
    }

    return (
        <DashboardLayout title="Dashboard - Dietary Preferences">
            <div className="crud-header space-between">
                <div>
                    <h1 className="crud-title">Dietary Preferences</h1>
                    <p className="text-muted">
                        Manage published nutrition dietary preferences.
                    </p>
                </div>

                <Link
                    href="/dashboard/dietary-preferences/create"
                    className="btn btn-fill btn-sm"
                >
                    <i className="fa-solid fa-plus"></i>
                    <span className="ml-05">Add Diet</span>
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
                        placeholder="Search dietary preferences..."
                    />
                    <button type="submit" className="search-btn">
                        Search
                    </button>
                </div>
            </form>

            {dietary_preferences.data.length === 0 && (
                <NotFoundSection
                    className="mt-1"
                    message="No Dietary Preferences Found"
                    description="There is no dietary preferences in the system."
                />
            )}

            {dietary_preferences.data.length > 0 && (
                <div className="crud-card-table mt-1">
                    <table className="crud-table">
                        <thead>
                            <tr>
                                <th className="no-min-width">Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            {dietary_preferences.data.map(
                                (dietary_preference) => (
                                    <tr key={dietary_preference.diet_code}>
                                        <td className="no-min-width">
                                            <div className="crud-image crud-image-contain">
                                                <img
                                                    src={
                                                        dietary_preference.public_image
                                                    }
                                                    alt={
                                                        dietary_preference.diet_name
                                                    }
                                                    loading="lazy"
                                                />
                                            </div>
                                        </td>

                                        <td>
                                            <h4 className="crud-table-title">
                                                {dietary_preference.diet_name}
                                            </h4>
                                            <div className="crud-sub">
                                                {dietary_preference.diet_code}
                                            </div>
                                        </td>

                                        <td>{dietary_preference.diet_desc}</td>

                                        <td className="text-right">
                                            <div className="crud-actions">
                                                <Link
                                                    href={`/dashboard/dietary-preferences/${dietary_preference.diet_code}/edit`}
                                                    className="icon-btn btn-secondary-outline"
                                                    title="Edit dietary preference"
                                                    aria-label="Edit dietary preference"
                                                >
                                                    <i className="fa-regular fa-pen-to-square" />
                                                </Link>

                                                <form
                                                    action=""
                                                    onSubmit={(e) =>
                                                        deleteDietaryPreference(
                                                            e,
                                                            dietary_preference.diet_code,
                                                        )
                                                    }
                                                >
                                                    <button
                                                        className="icon-btn btn-danger-outline"
                                                        title="Delete dietary preference"
                                                        aria-label="Delete dietary preference"
                                                    >
                                                        <i className="fa-regular fa-trash-can" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                ),
                            )}
                        </tbody>
                    </table>
                </div>
            )}

            <Paginator
                paginator={dietary_preferences}
                onNavigate={(url) => router.get(url)}
            />
        </DashboardLayout>
    );
}
