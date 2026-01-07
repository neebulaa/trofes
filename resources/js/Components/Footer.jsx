import { Link } from "@inertiajs/react";
export default function Footer(){
    return (
        <footer id="footer">
            <div className="container footer-center">
                <div className="footer-logo">
                    <Link href="/"> 
                        <img src="/assets/logo/logo-transparent.png" alt="logo" />
                    </Link>
                </div>
                <h3 className="footer-title">Trofes</h3>
                <p className="footer-sub">
                    Temukan rekomendasi makanan yang paling cocok untuk kebutuhan gizi Anda lebih mudah, lebih tepat, bersama Trofes.
                </p>

                <div className="socials">
                    <a href="#"><i className="fab fa-facebook-f"></i></a>
                    <a href="#"><i className="fab fa-linkedin-in"></i></a>
                    <a href="#"><i className="fab fa-instagram"></i></a>
                    <a href="#"><i className="fab fa-youtube"></i></a>
                </div>

                <div className="footer-bottom">
                    <p>@2025 Sevendeadlysins. All rights reserved.</p>
                    <div className="footer-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
    )
}