import { useState, useRef, useEffect } from "react";

export default function Dropdown({ options, value, onChange }) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(e) {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(e.target)
            ) {
                setOpen(false);
            }
        }

        document.addEventListener("mousedown", handleClickOutside);

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    return (
        <div ref={dropdownRef} className={`dropdown ${open ? "open" : ""}`}>
            <button
                type="button"
                className="dropdown-trigger"
                onClick={() => setOpen((prev) => !prev)}
            >
                <span>{value.label}</span>
                <i className="fa-solid fa-chevron-down"></i>
            </button>

            <div className="dropdown-menu">
                {options.map((option) => (
                    <div
                        key={option.value}
                        className={`dropdown-item ${
                            option.value === value.value ? "active" : ""
                        }`}
                        onClick={() => {
                            onChange(option);
                            setOpen(false);
                        }}
                    >
                        {option.label}
                    </div>
                ))}
            </div>
        </div>
    );
}