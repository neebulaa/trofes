import { Link } from "@inertiajs/react";

export default function GuideCard({ guide }) {
    return (
        <Link
            href={`/guides/${guide.slug}`}
            className="guide-card"
            key={guide.guide_id}
        >
            <div className="guide-card-image">
                <img src={guide.public_image} alt={guide.title} />
            </div>
            <h2 className="guide-card-title">{guide.title}</h2>
            <p className="guide-card-excerpt">{guide.excerpt}</p>
            <p className="guide-card-date">
                {new Date(guide.published_at).toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                })}
            </p>
        </Link>
    );
}
