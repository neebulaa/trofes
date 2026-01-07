import DasboardIcon from "../Dashboard/DashboardIcon";

export default function CollapsibleGroup({ title, icon, collapsed, defaultOpen = true, children }) {
    const [open, setOpen] = useState(defaultOpen);
    return (
        <div className={["dash-group", open ? "is-open" : ""].join(" ")}>
        <button
            type="button"
            className={["dash-group-btn", collapsed ? "is-collapsed" : ""].join(" ")}
            onClick={() => setOpen((v) => !v)}
        >
            <span className="dash-group-left">
            <span className="dash-nav-icon">
                <DashboardIcon name={icon} />
            </span>
            <span className="dash-group-title">{title}</span>
            </span>

            <span className="dash-group-chev" aria-hidden="true">
                <DashboardIcon name="chevronDown" />
            </span>
        </button>

        <div className="dash-group-content">{children}</div>
        </div>
    );
}