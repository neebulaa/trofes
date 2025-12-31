import { usePage } from '@inertiajs/react'
import Dropdown from './Dropdown'
import ProfileDropdown from './ProfileDropdown'
import { useEffect, useState } from 'react'
import NavLinks from './NavLinks'

export default function NavbarAuth({ user }) {
    const { url } = usePage()

    const [open, setOpen] = useState(false)

    const [openSearch, setOpenSearch] = useState(false)
    const [renderSearch, setRenderSearch] = useState(() => {
        return window.innerWidth > 768 ? true : false
    })

    useEffect(() => {
        function handleResize(){
            if(window.innerWidth <= 768){
                setRenderSearch(false)
                setOpenSearch(false)
            } else {
                setRenderSearch(true)
            }
        }

        window.addEventListener('resize', handleResize)

        return () => {
            window.removeEventListener('resize', handleResize)
        }
    }, [window.innerWidth]);

    const categoryOptions = [
        { label: 'Recipes', value: 'recipe' },
        { label: 'Guides', value: 'guides' },
    ]

    const [category, setCategory] = useState(categoryOptions[0])

    function openNavbar() {
        setOpen(!open)
    }

    function handleNavigate() {
        setOpen(false)
    }

    function openSearchBar() {
        if (!renderSearch) {
            setRenderSearch(true)
            requestAnimationFrame(() => {
                setOpenSearch(true)
            })
        } else {
            setOpenSearch(false)
        }
    }

    return (
        <header id="auth-navbar">
            <nav className={`container nav ${open ? 'nav-open' : ''}`}>
                <div className="logo">
                    <img
                        src="/assets/logo/logo-transparent.png"
                        alt="Trofes Logo"
                    />
                </div>

                <div
                    className="nav-search-toggle"
                    onClick={openSearchBar}
                >
                    <i className="fa-solid fa-magnifying-glass"></i>
                </div>

                {renderSearch && (
                    <div
                        className={`nav-search-container${
                            openSearch ? ' nav-search-open' : ''
                        }`}
                        onTransitionEnd={(e) => {
                            if (e.target == e.currentTarget && !openSearch) {
                                setRenderSearch(false)
                            }
                        }}
                    >
                        <div className="nav-search">
                            <div className="nav-search-icon">
                                <i className="fa-solid fa-magnifying-glass"></i>
                            </div>

                            <Dropdown
                                options={categoryOptions}
                                value={category}
                                onChange={setCategory}
                            />

                            <input
                                type="text"
                                placeholder="What are you looking for?"
                            />
                        </div>
                    </div>
                )}

                <div className="nav-content">
                    <NavLinks url={url} handleNavigate={handleNavigate} />
                </div>

                <div className="nav-content-auth">
                    <button
                        type="button"
                        className="custom-search-btn"
                    >
                        <i className="fa-brands fa-searchengin"></i>
                        <p>Custom</p>
                    </button>

                    <ProfileDropdown user={user} />
                </div>

                <button
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
    )
}
