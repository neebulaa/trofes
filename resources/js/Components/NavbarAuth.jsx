import { Link, useForm, usePage, router } from "@inertiajs/react";
import Dropdown from "./Dropdown";
import ProfileDropdown from "./ProfileDropdown";
import { useEffect, useState } from "react";
import NavLinks from "./NavLinks";

export default function NavbarAuth({ user }) {
    const { data, setData, errors } = useForm({
        search: "",
    });

    const { url } = usePage();

    const [open, setOpen] = useState(false);

    const [openSearch, setOpenSearch] = useState(false);
    const [renderSearch, setRenderSearch] = useState(() => {
        return window.innerWidth > 768 ? true : false;
    });

    useEffect(() => {
        function handleResize() {
            if (window.innerWidth <= 768) {
                setRenderSearch(false);
                setOpenSearch(false);
            } else {
                setRenderSearch(true);
            }
        }

        window.addEventListener("resize", handleResize);

        return () => {
            window.removeEventListener("resize", handleResize);
        };
    }, []);

    const categoryOptions = [
        { label: "Recipes", value: "recipes", route: "/recipes" },
        { label: "Guides", value: "guides", route: "/guides" },
    ];

    const [category, setCategory] = useState(categoryOptions[0]);

    function openNavbar() {
        setOpen(!open);
    }

    function handleNavigate() {
        setOpen(false);
    }

    function openSearchBar() {
        if (!renderSearch) {
            setRenderSearch(true);
            requestAnimationFrame(() => {
                setOpenSearch(true);
            });
        } else {
            setOpenSearch(false);
        }
    }

    function handleSearchSubmit(e) {
        e.preventDefault();

        const term = data.search?.trim();
        if (!term) return;

        router.get(
            category.route,
            { search: term },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            }
        );

        setOpenSearch(false);
    }

    return (
        <header id="auth-navbar">
        <nav className={`container nav ${open ? "nav-open" : ""}`}>
            <div className="logo">
                <Link href="/"> 
                    <img src="/assets/logo/logo-transparent.png" alt="Trofes Logo" />
                </Link>
            </div>

            <div className="nav-search-toggle" onClick={openSearchBar}>
            <i className="fa-solid fa-magnifying-glass"></i>
            </div>

            {renderSearch && (
            <form
                className={`nav-search-container${openSearch ? " nav-search-open" : ""}`}
                onTransitionEnd={(e) => {
                if (e.target === e.currentTarget && !openSearch) {
                    setRenderSearch(false);
                }
                }}
                onSubmit={handleSearchSubmit}
            >
                <div className="nav-search">
                <Dropdown
                    options={categoryOptions}
                    value={category}
                    onChange={setCategory}
                />

                <input
                    type="text"
                    placeholder="What are you looking for?"
                    value={data.search}
                    onChange={(e) => setData("search", e.target.value)}
                />

                <button type="submit" className="nav-search-icon-btn">
                    <i className="fa-solid fa-magnifying-glass"></i>
                </button>
                </div>

                {errors.search && <small className="error-text">{errors.search}</small>}
            </form>
            )}

            <div className="nav-content">
                <NavLinks url={url} handleNavigate={handleNavigate} />
            </div>

            <div className="nav-content-auth">
                <Link
                    href="/custom-search-recipes"
                    type="button"
                    className="custom-search-btn"
                >
                    <i className="fa-brands fa-searchengin"></i>
                    <p>Custom Search</p>
                </Link>

                <ProfileDropdown user={user} />
            </div>

            <button className="hamburger" aria-label="menu" onClick={openNavbar}>
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>
        </header>
    );
}
