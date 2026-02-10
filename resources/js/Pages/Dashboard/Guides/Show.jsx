import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import DashboardIcon from "../../../Components/Dashboard/DashboardIcon";

import '../../../../css/Dashboard/DashboardGuides.css';

export default function ShowGuide({ guide }) {
    return (
        <DashboardLayout title="Dashboard - Guides">
            <div className="crud-header">
                <Link href="/dashboard/guides" aria-label="Back">
                    <DashboardIcon name="chevronLeft" />
                </Link>
                <div>
                    <h1 className="crud-title">View Guide</h1>
                    <p className="text-muted">Look at the details of the guide.</p>
                </div>
            </div>

            <div className="crud-show">
                <div className="crud-card crud-guide-card">
                    <div className="guide-detail-date">
                        <i className="fa-regular fa-calendar"></i> {new Date(guide.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                    </div>
                    <h2 className="guide-title">{guide.title}</h2>
                    <div className="guide-detail-image">
                        <img
                            src={guide.public_image}
                            alt={guide.title}
                            />
                    </div>
                    <div className="mt-1" dangerouslySetInnerHTML={{ __html: guide.content }}></div>
                </div>
            </div>
        </DashboardLayout>
    );
}
