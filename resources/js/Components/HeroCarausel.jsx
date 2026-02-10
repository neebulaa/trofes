import { useEffect, useState } from "react";
import { Link } from "@inertiajs/react";
const cardsData = [
    {
        id: 1,
        img: "/assets/sample-images/nasi hainan.jpg",
        label: "Nasi Hainan Tiongkok",
    },
    { id: 2, img: "/assets/sample-images/sate ayam.jpg", label: "Sate Ayam" },
    {
        id: 3,
        img: "/assets/sample-images/nasi lemak.jpg",
        label: "Nasi Lemak Malaysia",
    },
    { id: 4, img: "/assets/sample-images/ramen sapi.jpg", label: "Beef Ramen" },
    {
        id: 5,
        img: "/assets/sample-images/chicken masala.jpg",
        label: "Chicken Masala",
    },
];

export default function HeroCarousel({ recipes }) {
    const [activeIndex, setActiveIndex] = useState(2);
    const total = recipes.length;

    useEffect(() => {
        const interval = setInterval(() => {
            setActiveIndex((prev) => (prev + 1) % total);
        }, 6000);

        return () => clearInterval(interval);
    }, [total]);

    const getPosition = (index) => {
        let diff = index - activeIndex;

        if (diff > 2) diff -= total;
        if (diff < -2) diff += total;

        if (diff > 2) diff = 2;
        if (diff < -2) diff = -2;

        return diff;
    };

    return (
        <div className="carousel-wrap">
            <div className="carousel-shell">
                <div className="carousel-coverflow">
                    {recipes.map((card, index) => {
                        const pos = getPosition(index);

                        return (
                            <Link
                                href={`/recipes/${card.slug}`}
                                key={card.recipe_id}
                                className="food-card"
                                style={{ "--x": pos }}
                            >
                                <img src={card.public_image} alt={card.title} />
                                <div className="recipe-card-badge for-name">
                                    <span className="badge-text">
                                        {card.title}
                                    </span>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
