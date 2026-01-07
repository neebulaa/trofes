import { Link, useForm, usePage } from '@inertiajs/react'
import { useState, useRef, useEffect } from 'react'

export default function ProfileDropdown({ user }) {
    const [open, setOpen] = useState(false)
    const dropdownRef = useRef(null)
    const {url} = usePage()

    const { post, processing } = useForm()

    const isActive = (path) => {
        return url.startsWith(path);
    };

    useEffect(() => {
        function handleClickOutside(e) {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setOpen(false)
            }
        }

        document.addEventListener('mousedown', handleClickOutside)
        return () => document.removeEventListener('mousedown', handleClickOutside)
    }, [])

    const handleSignOut = (e) => {
        e.preventDefault()

        post('/sign-out')
    }

    return (
        <div
            ref={dropdownRef}
            className={`profile-dropdown ${open ? 'open' : ''}`}
        >
            <div
                className="user-profile"
                onClick={() => setOpen(prev => !prev)}
            >
                <img
                    src={user.public_profile_image}
                    alt={`profile-${user.username}`}
                />
            </div>

            <div className="dropdown-menu">
                <div className="profile-dropdown-desc">
                    <img
                        src={user.public_profile_image}
                        alt={`profile-${user.username}`}
                    />
                    <div className="profile-info">
                        <h4>{user.username}</h4>
                        <p>{user.full_name}</p>
                    </div>
                </div>

                {
                    user.is_admin == 1 && 
                    <Link 
                    className={`dropdown-item ${isActive('/dashboard') ? 'active' : ''}`}
                    href="/dashboard">
                        <i class="fa-solid fa-border-all"></i>
                        <p>Dashboard</p>
                    </Link>
                }

                <Link 
                className={`dropdown-item ${isActive('/profile') ? 'active' : ''}`}
                href="/profile">
                    <i className="fa-regular fa-user"></i>
                    <p>Profile</p>
                </Link>

                <div className="dropdown-item">
                    <i className="fa-solid fa-arrow-right-from-bracket"></i>
                    <button
                        type="button"
                        onClick={handleSignOut}
                        disabled={processing}
                        className="signout-btn"
                    >
                        {processing ? 'Signing out...' : 'Sign Out'}
                    </button>
                </div>
            </div>
        </div>
    )
}
