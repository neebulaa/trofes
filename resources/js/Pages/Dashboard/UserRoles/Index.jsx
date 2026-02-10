import DashboardLayout from "../../../Layouts/DashboardLayout";
import NotFoundSection from "../../../Components/NotFoundSection";
import FlashMessage from "../../../Components/FlashMessage";
import { useState, useEffect, useRef, useCallback } from "react";
import { router, usePage, useRemember } from "@inertiajs/react";

import "../../../../css/Dashboard/DashboardUserRoles.css";

export default function UserRoles({ users, filters }) {
    const {
        auth: { user: authUser },
    } = usePage().props;

    const [search, setSearch] = useState(filters?.search || "");
    const [edited, setEdited] = useRemember({}, "user-role-edits");

    const [items, setItems] = useState(users.data);
    const [nextUrl, setNextUrl] = useState(users.next_page_url);

    const loadMoreRef = useRef(null);
    const scrollRef = useRef(null);
    const isAppendingRef = useRef(false);

    // keep search in sync if filters change
    useEffect(() => {
        setSearch(filters?.search || "");
    }, [filters?.search]);

    // when new server props arrive (search or refresh), reset list
    useEffect(() => {
        if (isAppendingRef.current) {
            isAppendingRef.current = false;
            return;
        }
        setItems(users.data);
        setNextUrl(users.next_page_url);
    }, [users]);

    // live search (server-side
    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get(
                "/dashboard/roles",
                { search },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [search]);

    const toggleAdmin = (user_id, current) => {
        setEdited((prev) => ({
            ...prev,
            [user_id]: !current,
        }));
    };

    const handleSave = () => {
        const payload = Object.entries(edited).map(([user_id, is_admin]) => ({
            user_id: Number(user_id),
            is_admin,
        }));

        if (payload.length === 0) return;

        router.post(
            "/dashboard/roles/assign",
            { users: payload },
            {
                preserveScroll: true,
                onSuccess: () => setEdited({}),
            },
        );
    };

    const loadMore = useCallback(() => {
        if (!nextUrl || isAppendingRef.current) return;

        isAppendingRef.current = true;

        router.get(
            nextUrl,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onSuccess: (page) => {
                    const newUsers = page.props.users.data;
                    setItems((prev) => [...prev, ...newUsers]);
                    setNextUrl(page.props.users.next_page_url);
                },
            },
        );
    }, [nextUrl]);

    // observer for infinite scroll (inside container)
    useEffect(() => {
        if (!loadMoreRef.current || !scrollRef.current) return;

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) loadMore();
            },
            {
                root: scrollRef.current,
                rootMargin: "200px",
            },
        );

        observer.observe(loadMoreRef.current);

        return () => observer.disconnect();
    }, [loadMore]);

    return (
        <DashboardLayout title="Dashboard - User Roles" id="user-roles">
            <div className="crud-header space-between">
                <div>
                    <h1 className="crud-title">User Roles</h1>
                    <p className="text-muted">Manage published user roles.</p>
                </div>
            </div>

            <FlashMessage className="mt-1" />

            <div className="search-input mt-1">
                <span>
                    <i className="fa-solid fa-magnifying-glass"></i>
                </span>
                <input
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search users..."
                />
                <button type="button" className="search-btn">
                    Search
                </button>
            </div>

            {items.length === 0 && (
                <NotFoundSection
                    className="mt-1"
                    message="No Users Found"
                    description="There are no users in the system."
                />
            )}

            {items.length > 0 && (
                <>
                    <div ref={scrollRef} className="crud-card-table mt-1">
                        <table className="crud-table">
                            <thead>
                                <tr>
                                    <th className="no-min-width">Profile</th>
                                    <th>Name</th>
                                    <th className="table-column-seperator table-data-center">
                                        Admin
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((user) => {
                                    const isAdmin =
                                        edited[user.user_id] ?? user.is_admin;

                                    return (
                                        <tr key={user.user_id}>
                                            <td className="no-min-width">
                                                <div className="crud-image crud-image-contain crud-image-circle">
                                                    <img
                                                        src={
                                                            user.public_profile_image
                                                        }
                                                        alt={user.username}
                                                        loading="lazy"
                                                    />
                                                </div>
                                            </td>

                                            <td>
                                                <h4 className="crud-table-title">
                                                    {user.full_name}
                                                </h4>
                                                <div className="crud-sub">
                                                    {user.username}
                                                </div>
                                            </td>

                                            <td className="table-column-seperator">
                                                <input
                                                    type="checkbox"
                                                    checked={isAdmin}
                                                    onChange={() =>
                                                        toggleAdmin(
                                                            user.user_id,
                                                            isAdmin,
                                                        )
                                                    }
                                                    readOnly={
                                                        user.user_id ===
                                                        authUser.user_id
                                                    }
                                                    disabled={
                                                        user.user_id ===
                                                        authUser.user_id
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>

                        {/* sentinel inside scroll container */}
                        <div ref={loadMoreRef} />
                    </div>

                    <div className="mt-2">
                        <button className="btn btn-fill" onClick={handleSave}>
                            Save Changes
                        </button>
                    </div>
                </>
            )}
        </DashboardLayout>
    );
}
