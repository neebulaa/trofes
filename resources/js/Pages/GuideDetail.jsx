export default function GuideDetail({guide}) {
    return (
        <section className="guide-detail" id="guide-detail">
            <div className="container guide-detail-container">
                <h1 className="guide-detail-title">{guide.title}</h1>
                <p className="guide-detail-content">{guide.content}</p>
            </div>
        </section>
    );
}