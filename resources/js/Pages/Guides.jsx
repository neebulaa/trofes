import Layout from "../Layouts/Layout";

import "../../css/GuidesPage.css";
import { router } from "@inertiajs/react";
import Paginator from "../Components/Paginator";

export default function Guides({guides, filters}){
    return (
        <>
        <section className="guides-page" id="guides-page">
            <div className="container guides-container">
                <h1 className="guides-page-title">
                    Ayo <span className="green-block">Belajar</span> Bersama
                </h1>
                <p className="guides-page-about">Temukan berbagai tutorial yang telah kami siapkan untuk membantu Anda memahami setiap langkah dengan lebih mudah. Mulai dari panduan dasar hingga tips lanjutan, semuanya dirancang agar proses belajar terasa ringan, jelas, dan menyenangkan. Yuk, jelajahi materi yang sudah kami buat dan kembangkan kemampuan Anda bersama kami!</p>

                <div className="search-and-filters">
                    <input
                        type="text"
                        className="search-input"
                        placeholder="Cari panduan..."
                        defaultValue={filters.search}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                const url = new URL(window.location);
                                url.searchParams.set('search', e.target.value);
                                window.location.href = url.toString();
                            }
                        }}
                    />
                </div>

                <div className="guides-page-list">
                    {guides.data.map((guide) => (
                        <div className="guide-card" key={guide.guide_id}>
                            <div className="guide-card-image">
                                <img src={guide.image} alt={guide.title} />
                            </div>
                            <h2 className="guide-card-title">{guide.title}</h2>
                            <p className="guide-card-excerpt">{guide.excerpt}</p>
                            <p className="guide-card-date">{new Date(guide.published_at).toLocaleDateString()}</p>
                        </div>
                    ))}
                </div>

                <div className="guides-page-paginator">
                    <Paginator paginator={guides} onNavigate={(url) => router.get(url)} />
                </div>
            </div>
        </section>
        </>
    );
}

Guides.layout = page => <Layout children={page}/>