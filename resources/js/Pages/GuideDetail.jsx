import { Link } from "@inertiajs/react";
import "../../css/GuideDetail.css";
import Layout from "../Layouts/Layout";

export default function GuideDetail({guide, next_guide, prev_guide, other_guides}) {
    return (
        <section className="guide-detail" id="guide-detail">
            <div className="container guide-detail-container">
                <div className="guide-content">
                    <Link href="/guides">
                        <div className="guide-detail-date">
                                <i className="fa-solid fa-chevron-left"></i>
                            <i className="fa-regular fa-calendar"></i> {new Date(guide.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                        </div>
                    </Link>
                    <h1 className="guide-detail-title">{guide.title}</h1>
                    <div className="guide-detail-image">
                        <img src={guide.public_image} alt={guide.title} />
                    </div>

                    <p className="guide-detail-content"
                        dangerouslySetInnerHTML={{ __html: guide.content }}
                    />

                    <div className="guide-detail-navigation">
                        <h2>Great job! Time to keep <span className="green-block">Learning!</span></h2>
                        <p>You've completed an important step. Now, let's continue your journey toward a more nutritious lifestyle.</p>

                        <div className="guide-navigators">
                            {prev_guide && (
                                <Link href={`/guides/${prev_guide.slug}`} className="btn btn-line-white"> 
                                    <i className="fa-solid fa-chevron-left"></i>Previous
                                </Link>
                            )}
                            {next_guide && (
                                <Link href={`/guides/${next_guide.slug}`} className="btn btn-line-white">Next  <i className="fa-solid fa-chevron-right"></i>
                                </Link>
                            )}
                        </div>
                    </div>
                </div>
                
                <div className="guide-other">
                    <h2 className="guide-other-title">Other Guides</h2>
                    <div className="guide-other-list">
                        {other_guides.map((other_guide) => (
                            <Link href={`/guides/${other_guide.slug}`} className="guide-other-item" key={other_guide.guide_id}>
                                <div className="guide-other-item-image">
                                    <img src={other_guide.public_image} alt={other_guide.title} />
                                </div>

                                <div className="guide-other-item-content">
                                    <h3>{other_guide.title}</h3>
                                    <p>{other_guide.excerpt}...</p>
                                    <p className="date">{new Date(other_guide.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

GuideDetail.layout = (page) => <Layout children={page} />;
