export default function NotFoundSection({className, message, description}){
    return (
        <section className={`not-found-section ${className}`}>
            <div className="container">
                <div className="not-found-image">
                    <img src="/assets/logo/logo-transparent.png" alt="Trofes Not Found" />
                </div>
                <h2>{message || "Content Not Found"}</h2>
                <p>{description || "Sorry, we couldn't find the content you were looking for."}</p>
            </div>
        </section>
    )
}