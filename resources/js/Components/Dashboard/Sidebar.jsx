import { useMemo } from "react";
import { Link, usePage } from "@inertiajs/react";
import DashboardIcon from "../Dashboard/DashboardIcon";
import NavLinkItem from "../Dashboard/NavLinkItem";
import CollapsibleGroup from "../Dashboard/CollapsibleGroup";

function isActive(currentUrl, href) {
    if (!href) return false;
    return currentUrl === href || (href !== "/dashboard" && currentUrl.startsWith(href));
}

export default function Sidebar({ collapsed, mobileOpen, onToggleCollapsed, onCloseMobile }) {
    const { url } = usePage();

    const mainNavItems = useMemo(() => [
        { type: "link", label: "Home", href: "/dashboard", icon: "home" },
        // { type: "group", title: "Users", href: "/users", icon: "home", 
        //     items: [
        //         { type: "link", label: "Admin", href: "/dashboard/users", icon: "guides" },
        //     ],
        //     defaultOpen: true,
        // },
        { type: "link", label: "Message", href: "/dashboard/messages", icon: "messages" },
        { type: "link", label: "Guides", href: "/dashboard/guides", icon: "guides" },
        { type: "link", label: "Allergies", href: "/dashboard/allergies", icon: "allergies" },
        { type: "link", label: "Dietary", href: "/dashboard/dietary-preferences", icon: "diet" },
        { type: "link", label: "Roles", href: "/dashboard/roles", icon: "roles" },
    ], []);

    return (
        <aside
            className={[
                "dash-sidebar",
                collapsed && !mobileOpen ? "is-collapsed" : "",
                mobileOpen ? "is-mobile-open" : "",
            ].join(" ")}
            aria-label="Dashboard sidebar"
        >
        <div className="dash-sidebarTop">
            <Link href="/">
                <div className="dash-brand">
                    <div className="dash-brand-logo">
                        <img src="/assets/logo/logo-transparent.png" alt="Trofes Logo Dashboard" />
                    </div>

                    <div className="dash-brand-text">
                        <h3 className="">Trofes</h3>
                        <span className="text-muted">Admin Dashboard</span>
                    </div>
                </div>
            </Link>

            <div className="dash-sideActions">
                <button
                    type="button"
                    className="dash-iconBtn dash-iconBtn--desktop"
                    onClick={onToggleCollapsed}
                    title={collapsed ? "Expand sidebar" : "Collapse sidebar"}
                    aria-label="Toggle collapse sidebar"
                >
                    <DashboardIcon name="menu" />
                </button>

                <button
                    type="button"
                    className="dash-iconBtn dash-iconBtn--mobileClose"
                    onClick={onCloseMobile}
                    aria-label="Close sidebar"
                    title="Close"
                >
                    <DashboardIcon name="close" />
                </button>
            </div>
        </div>

        <div className="dash-sidebarBody">
            <div className="dash-nav-sectionLabel">Main</div>

            <nav className="dash-nav">
                {mainNavItems.map((item, idx) => {
                    if (item.type === "divider") {
                        return <div key={`div-${idx}`} className="dash-nav-divider" />;
                    }

                    if (item.type === "link") {
                        return (
                            <NavLinkItem
                                key={item.href}
                                {...item}
                                collapsed={collapsed}
                                active={isActive(url, item.href)}
                            />
                        );
                    }

                    if (item.type === "group") {
                        return (
                            <CollapsibleGroup
                                key={item.title}
                                title={item.title}
                                icon={item.icon}
                                collapsed={collapsed}
                                defaultOpen={item.defaultOpen}
                            >
                            <div className="dash-nav dash-nav--nested">
                                {item.items.map((sub) => (
                                    <NavLinkItem
                                        key={sub.href}
                                        {...sub}
                                        collapsed={collapsed}
                                        active={isActive(url, sub.href)}
                                    />
                                ))}
                            </div>
                            </CollapsibleGroup>
                        );
                    }

                    return null;
                })}
            </nav>
        </div>
        </aside>
    );
}
