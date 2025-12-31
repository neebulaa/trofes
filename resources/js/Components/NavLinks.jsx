import {Link} from '@inertiajs/react'
export default function NavLinks({url, handleNavigate}){
    const isActive = (path) => url === path
    return (
        <div className="nav-links">
            <Link
                href="/"
                className={isActive('/') ? 'active' : ''}
                onClick={handleNavigate}
            >
                Home
            </Link>

            <Link
                href="/recipes"
                className={isActive('/recipes') ? 'active' : ''}
                onClick={handleNavigate}
            >
                Recipes
            </Link>

            <Link
                href="/guides"
                className={isActive('/guides') ? 'active' : ''}
                onClick={handleNavigate}
            >
                Guides
            </Link>

            <Link
                href="/contact-us"
                className={isActive('/contact-us') ? 'active' : ''}
                onClick={handleNavigate}
            >
                Contact Us
            </Link>
        </div>
    );
}