import { useMemo, useState } from "react";
import { Head, usePage } from '@inertiajs/react';
import Sidebar from "../Components/Dashboard/Sidebar";
import Navbar from "../Components/Dashboard/Navbar";
import "../../css/Dashboard/Dashboard.css";


export default function DashboardLayout({ children, title = "Dashboard" }) {
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
    const [sidebarMobileOpen, setSidebarMobileOpen] = useState(false);
    const { url, props } = usePage();
    const { auth: { user } } = props;

    const layoutClass = useMemo(() => {
        return [
            "dash",
            sidebarCollapsed ? "dash--sidebar-collapsed" : "",
            sidebarMobileOpen ? "dash--mobile-open" : "",
        ]
        .filter(Boolean)
        .join(" ");
    }, [sidebarCollapsed, sidebarMobileOpen]);

    return (
        <>
            <Head>
                <title>Trofes Dashboard</title>

                <meta
                    name="description"
                    content="Trofes is a smart nutrition assistant that provides personalized meal recommendations based on your needs, helping you choose healthier, practical, and simple food options every day."
                />
                <meta
                    name="keywords"
                    content="Trofes, smart nutrition assistant, food recommendation, healthy meals, personalized nutrition, diet assistant, daily meal planning"
                />
                <meta name="author" content="Trofes" />
                <meta name="robots" content="index, follow" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />

                <link rel="icon" href="/assets/logo/logo-transparent.png" />
                <link
                    rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
                    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
                    crossOrigin="anonymous"
                    referrerPolicy="no-referrer"
                />
            </Head>

            <div className={layoutClass}>
                {/* Mobile overlay */}
                <button
                    className="dash-overlay"
                    onClick={() => setSidebarMobileOpen(false)}
                    aria-label="Close sidebar overlay"
                />

                <Sidebar
                    collapsed={sidebarCollapsed}
                    mobileOpen={sidebarMobileOpen}
                    onToggleCollapsed={() => setSidebarCollapsed((v) => !v)}
                    onCloseMobile={() => setSidebarMobileOpen(false)}
                />

                <div className="dash-main">
                    <Navbar
                        user={user}
                        title={title}
                        onOpenSidebarMobile={() => setSidebarMobileOpen(true)}
                    />

                    <main className="dash-content">
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
}
