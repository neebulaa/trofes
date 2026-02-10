/**
 * Supports BOTH shapes:
 *
 * 1) Non-resource paginator (plain paginate()):
 * {
 *   current_page, last_page,
 *   prev_page_url, next_page_url,
 *   links: [{ url, label, active }, ...]
 * }
 *
 * 2) Resource paginator (Resource::collection($paginator)):
 * {
 *   meta: { current_page, last_page, links: [...] },
 *   links: { prev, next, first, last }
 * }
 */
export default function Paginator({
    paginator,
    onNavigate,
    className = "",
    maxVisible = window.innerWidth < 640 ? 2 : 5,
}) {
    if (!paginator) return null;

    // resource/non-resource normalize into one shape
    const isResourceShape = !!paginator.meta;

    const current_page = isResourceShape
        ? paginator.meta.current_page
        : paginator.current_page;

    const last_page = isResourceShape
        ? paginator.meta.last_page
        : paginator.last_page;

    const linksArray = isResourceShape ? paginator.meta.links : paginator.links;

    const prevUrl = isResourceShape
        ? paginator.links?.prev
        : paginator.prev_page_url;

    const nextUrl = isResourceShape
        ? paginator.links?.next
        : paginator.next_page_url;

    if (!last_page || last_page <= 1) return null;
    if (!Array.isArray(linksArray)) return null;

    const navigate = (url) => {
        if (!url) return;
        if (onNavigate) return onNavigate(url);
        window.location.href = url;
    };

    const pageLinks = linksArray.filter((l) => {
        const label = String(l.label).toLowerCase();
        return label !== "previous" && label !== "next";
    });

    // truncation logic nih
    const half = Math.floor(maxVisible / 2);
    let start = Math.max(current_page - half, 1);
    let end = Math.min(start + maxVisible - 1, last_page);

    if (end - start + 1 < maxVisible) {
        start = Math.max(end - maxVisible + 1, 1);
    }

    const visiblePages = pageLinks.filter((l) => {
        const page = Number(l.label);
        return Number.isFinite(page) && page >= start && page <= end;
    });

    const showFirst = start > 1;
    const showLast = end < last_page;

    const firstLink = pageLinks.find((l) => Number(l.label) === 1);
    const lastLink = pageLinks.find((l) => Number(l.label) === last_page);

    return (
        <nav className={`paginator ${className}`} aria-label="Pagination">
            <button
                type="button"
                className="paginator-btn"
                onClick={() => navigate(prevUrl)}
                disabled={!prevUrl}
            >
                &lt;
            </button>

            <div className="paginator-pages">
                {showFirst && firstLink?.url && (
                    <>
                        <button
                            type="button"
                            className="paginator-page"
                            onClick={() => navigate(firstLink.url)}
                        >
                            1
                        </button>
                        <span className="paginator-ellipsis">…</span>
                    </>
                )}

                {visiblePages.map((link, idx) => (
                    <button
                        key={`${link.label}-${idx}`}
                        type="button"
                        className={`paginator-page ${
                            link.active ? "is-active" : ""
                        }`}
                        onClick={() => navigate(link.url)}
                        disabled={!link.url || link.active}
                        aria-current={link.active ? "page" : undefined}
                    >
                        {link.label}
                    </button>
                ))}

                {showLast && lastLink?.url && (
                    <>
                        <span className="paginator-ellipsis">…</span>
                        <button
                            type="button"
                            className="paginator-page"
                            onClick={() => navigate(lastLink.url)}
                        >
                            {last_page}
                        </button>
                    </>
                )}
            </div>

            <button
                type="button"
                className="paginator-btn"
                onClick={() => navigate(nextUrl)}
                disabled={!nextUrl}
            >
                &gt;
            </button>
        </nav>
    );
}
