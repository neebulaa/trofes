import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import NotFoundSection from "../../../Components/NotFoundSection";
import Paginator from "../../../Components/Paginator";
import { useForm } from "@inertiajs/react";
import FlashMessage from "../../../Components/FlashMessage";
import { router } from "@inertiajs/react";

export default function Allergies({ allergies }) {
    const { delete: destroy } = useForm();
    const { data, setData, get, errors } = useForm({
        search: "",
    });

    function deleteAllergy(e, allergy_code) {
        e.preventDefault();
        if (confirm("Are you sure you want to delete this allergy?")) {
            destroy(`/dashboard/allergies/${allergy_code}`, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        get("/dashboard/allergies", {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            data: { search: data.search },
        });
    }

    return (
        <DashboardLayout title="Dashboard - Allergies">
            <div className="crud-header space-between">
                <div>
                    <h1 className="crud-title">Allergies</h1>
                    <p className="text-muted">
                        Manage published nutrition allergies.
                    </p>
                </div>

                <Link
                    href="/dashboard/allergies/create"
                    className="btn btn-fill btn-sm"
                >
                    <i className="fa-solid fa-plus"></i>
                    <span className="ml-05">Add Allergy</span>
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
                        placeholder="Search allergies..."
                    />
                    <button type="submit" className="search-btn">
                        Search
                    </button>
                </div>
            </form>

            {allergies.data.length === 0 && (
                <NotFoundSection
                    className="mt-1"
                    message="No Allergies Found"
                    description="There is no allergies in the system."
                />
            )}

            {allergies.data.length > 0 && (
                <div className="crud-card-table mt-1">
                    <table className="crud-table">
                        <thead>
                            <tr>
                                <th className="no-min-width">Image</th>
                                <th>Name</th>
                                <th>Examples</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            {allergies.data.map((allergy) => (
                                <tr key={allergy.allergy_code}>
                                    <td className="no-min-width">
                                        <div className="crud-image crud-image-contain">
                                            <img
                                                src={allergy.public_image}
                                                alt={allergy.allergy_name}
                                                loading="lazy"
                                            />
                                        </div>
                                    </td>

                                    <td>
                                        <h4 className="crud-table-title">
                                            {allergy.allergy_name}
                                        </h4>
                                        <div className="crud-sub">
                                            {allergy.allergy_code}
                                        </div>
                                    </td>

                                    <td>{allergy.examples}</td>

                                    <td className="text-right">
                                        <div className="crud-actions">
                                            <Link
                                                href={`/dashboard/allergies/${allergy.allergy_code}/edit`}
                                                className="icon-btn btn-secondary-outline"
                                                title="Edit allergy"
                                                aria-label="Edit allergy"
                                            >
                                                <i className="fa-regular fa-pen-to-square" />
                                            </Link>

                                            <form
                                                action=""
                                                onSubmit={(e) =>
                                                    deleteAllergy(
                                                        e,
                                                        allergy.allergy_code,
                                                    )
                                                }
                                            >
                                                <button
                                                    className="icon-btn btn-danger-outline"
                                                    title="Delete allergy"
                                                    aria-label="Delete allergy"
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
                paginator={allergies}
                onNavigate={(url) => router.get(url)}
            />
        </DashboardLayout>
    );
}
