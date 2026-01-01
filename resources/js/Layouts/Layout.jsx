import '../../css/Navbar.css'
import '../../css/Footer.css'
import NavbarGuest from '../Components/NavbarGuest'
import NavbarAuth from '../Components/NavbarAuth'
import Footer from '../Components/Footer'

import { Head, usePage } from '@inertiajs/react';

export default function Layout({ children }) {
    const { url, props } = usePage();
    const { auth: { user } } = props;

    const hideLayout =
    url.startsWith('/reset-password') ||
    ['/login', '/sign-up', '/onboarding', '/forgot-password'].includes(url);

    return (
        <>
            <Head>
                <title>Trofes</title>

                <meta
                    name="description"
                    content="Trofes is a smart nutrition assistant that provides personalized meal recommendations based on your needs, helping you choose healthier, practical, and simple food options every day."
                />
                <meta
                    name="keywords"
                    content="Trofes, smart nutrition assistant, food recommendation, healthy meals, personalized nutrition, diet assistant, daily meal planning"
                />
                <meta name="author" content="Trofes" />
                <meta name="robots" content="index, follow" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />

                <link rel="icon" href="/assets/logo/logo-transparent.png" />
                <link
                    rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
                    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
                    crossOrigin="anonymous"
                    referrerPolicy="no-referrer"
                />
            </Head>

            {!hideLayout && (user ? <NavbarAuth user={user}/> : <NavbarGuest />)}

            {children}

            {!hideLayout && <Footer />}
        </>
    );
}
