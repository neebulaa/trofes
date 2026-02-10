import GuideCard from "../../Components/GuideCard";
import {Link} from '@inertiajs/react';

export default function Guides({guides}){
    return (
        <section className="guides" id="guides">
            <div className="container">
                <div className="guides-head">
                    <h2 className="guides-title">Healty guides for you</h2>
                    <img
                        src="/assets/graphics/highlight.png"
                        alt=""
                        className="guides-scribble"
                    />
                    <div className="guides-desc">
                        <div className="guides-cta-box">
                            <p className="about-text">
                                Understand more about the nutrients you need to support a healthy lifestyle. Make every food choice more accurate and easier to decide for your body.
                            </p>
                            <Link href="/guides" className="btn btn-line">Learn more </Link>
                        </div>
                    </div>
                </div>

                <div className="guides-page-list">
                    {guides.map(guide => (
                        <GuideCard guide={guide} key={guide.guide_id}/>
                    ))}
                </div>
            </div>
        </section>
    )
}
