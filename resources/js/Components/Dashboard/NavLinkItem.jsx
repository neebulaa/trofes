import { Link } from "@inertiajs/react";
import Icon from "./DashboardIcon";

export default function NavLinkItem({ href, icon, label, collapsed, active }) {
    return (
        <Link
            href={href}
            className={[
                "dash-nav-link",
                active ? "is-active" : "",
                collapsed ? "is-collapsed" : "",
            ].join(" ")}
        >
            <span className="dash-nav-icon">
                <Icon name={icon} />
            </span>
            <span className="dash-nav-label">{label}</span>
        </Link>
    );
}