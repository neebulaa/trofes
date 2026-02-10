import { usePage } from "@inertiajs/react";
import { useState, useRef, useEffect } from "react";
import NavLinks from "./NavLinks";
import { Link } from "@inertiajs/react";

export default function Navbar() {
    const [open, setOpen] = useState(false);
    const { url } = usePage();

    function openNavbar() {
        setOpen(!open);
    }

    function handleNavigate() {
        setOpen(false);
    }

    const navRef = useRef(null);
    const hamburgerRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(e) {
            if (!open) return;
            const clickedInsideNav = navRef.current?.contains(e.target);
            const clickedHamburger = hamburgerRef.current?.contains(e.target);

            if (!clickedInsideNav && !clickedHamburger) {
                setOpen(false);
            }
        }

        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [open]);

    return (
        <header id='guest-navbar'>
            <nav className={`container nav ${open ? "nav-open" : ""}`}>
                <div className="logo">
                    <Link href="/">
                        <img
                            src="/assets/logo/logo-transparent.png"
                            alt="Trofes Logo"
                        />
                    </Link>
                </div>
                <div className="nav-content" ref={navRef}>
                    <NavLinks url={url} handleNavigate={handleNavigate} />

                    <div className="nav-action">
                        <Link href="/login">Log In</Link>
                        <Link className="btn btn-pill" href="/sign-up">
                            Sign Up
                        </Link>
                    </div>
                </div>

                <button
                    ref={hamburgerRef}
                    className="hamburger"
                    aria-label="menu"
                    onClick={openNavbar}
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </header>
    );
}
