export default function Icon ({ name }) {
    const map = {
        home: <i className="fa-regular fa-house" aria-hidden="true" />,
        guides: <i className="fa-solid fa-book-open" aria-hidden="true" />,
        users: <i className="fa-regular fa-users" aria-hidden="true" />,
        report: <i className="fa-regular fa-chart-bar" aria-hidden="true" />,
        settings: <i className="fa-regular fa-gear" aria-hidden="true" />,
        chevronDown: <i className="fa-solid fa-chevron-down" aria-hidden="true" />,
        chevronLeft: <i className="fa-solid fa-chevron-left" aria-hidden="true" />,
        menu: <i className="fa-solid fa-bars" aria-hidden="true" />,
        close: <i className="fa-solid fa-xmark" aria-hidden="true" />,
        allergies: <i className="fa-solid fa-shrimp"></i>,
        diet: <i className="fa-regular fa-lemon"></i>,
        roles: <i className="fa-regular fa-address-card"></i>,
        messages: <i className="fa-regular fa-envelope"></i>,
    };

    return (
        <span className="dash-ic" aria-hidden="true">
            {map[name] ?? <i className="fa-solid fa-circle" aria-hidden="true" />}
        </span>
    );
};
