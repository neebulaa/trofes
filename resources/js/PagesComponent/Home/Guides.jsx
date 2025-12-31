import GuideCard from "../../Components/GuideCard";
import {Link} from '@inertiajs/react';

export default function Guides({guides}){
    return (
        <section className="guides" id="guides">
            <div className="container">
                <div className="guides-head">
                    <h2 className="guides-title">Panduan  Untukmu</h2>
                    <img
                        src="/assets/graphics/highlight.png"
                        alt=""
                        className="guides-scribble"
                    />
                    <div className="guides-desc">
                        <div className="guides-cta-box">
                            <p className="about-text">
                                Pahami lebih banyak tentang nutrisi yang Anda butuhkan untuk mendukung gaya hidup sehat dan membuat setiap pilihan makanan lebih tepat.
                            </p>
                            <Link href="/guides" className="btn btn-line">Pelajari Lebih Banyak</Link>
                        </div>
                    </div>
                </div>

                <div className="guides-grid">
                    {guides.map(guide => (
                        <GuideCard guide={guide} key={guide.guide_id}/>
                    ))}
                </div>
            </div>
        </section>
    )
}
