import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import NotFoundSection from "../../../Components/NotFoundSection";
import Paginator from "../../../Components/Paginator";
import { useForm } from "@inertiajs/react";
import FlashMessage from "../../../Components/FlashMessage";
import { router } from "@inertiajs/react";

export default function Messages({ messages }) {
    const { delete: destroy } = useForm();
    const { data, setData, get, errors } = useForm({
        search: "",
    });

    function deleteMessage(e, message_id) {
        e.preventDefault();
        if (confirm("Are you sure you want to delete this message?")) {
            destroy(`/dashboard/messages/${message_id}`, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        get("/dashboard/messages", {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            data: { search: data.search },
        });
    }

    return (
        <DashboardLayout title="Dashboard - Messages">
            <div className="crud-header space-between">
                <div>
                    <h1 className="crud-title">Messages</h1>
                    <p className="text-muted">Manage received messages.</p>
                </div>
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
                        placeholder="Search messages..."
                        />
                    <button
                        type="submit"
                        className="search-btn"
                    >
                        Search
                    </button>
                </div>
            </form>

            {messages.data.length === 0 && (
                <NotFoundSection
                    className="mt-1"
                    message="No Messages Found"
                    description="There are no messages in the system."
                />
            )}

            {messages.data.length > 0 && (
                <div className="crud-card-table mt-1">
                    <table className="crud-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Message Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            {messages.data.map((message) => (
                                <tr key={message.id}>
                                    <td>
                                        <h4 className="crud-table-title">
                                            {message.name}
                                        </h4>
                                        <div className="crud-sub">
                                            {message.email}
                                        </div>
                                    </td>

                                    <td>{message.message}</td>

                                    <td className="text-right">
                                        <div className="crud-actions">
                                            <form
                                                action=""
                                                onSubmit={(e) =>
                                                    deleteMessage(
                                                        e,
                                                        message.message_id,
                                                    )
                                                }
                                            >
                                                <button
                                                    className="icon-btn btn-danger-outline"
                                                    title="Delete message"
                                                    aria-label="Delete message"
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
                paginator={messages}
                onNavigate={(url) => router.get(url)}
            />
        </DashboardLayout>
    );
}
