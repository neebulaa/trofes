import { useState } from "react";
import ProfileDropdown from "../ProfileDropdown";
import DashboardIcon from "../Dashboard/DashboardIcon";

export default function Navbar({ title, onOpenSidebarMobile, user }) {
    const [profileOpen, setProfileOpen] = useState(false);

    return (
        <header className="dash-navbar">
            <div className="dash-navbarLeft">
                <button
                    type="button"
                    className="dash-iconBtn dash-iconBtn--mobileOpen"
                    onClick={onOpenSidebarMobile}
                    aria-label="Open sidebar"
                    title="Menu"
                >
                    <DashboardIcon name="menu" />
                </button>

                <div className="dash-title">
                    <h1>{title}</h1>
                    <p className="text-muted">Welcome back, {user?.username ?? 'user'} 👋</p>
                </div>
            </div>

            <div className="dash-navbarRight">
                <ProfileDropdown user={user} />
            </div>
        </header>
    );
}
