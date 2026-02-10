import { useState } from "react";
import { usePage } from "@inertiajs/react";

export default function FlashMessage({ className }) {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(true);

    if (!flash?.message || !visible) return null;

    return (
        <div className={`flash flash-${flash.type} ${className}`}>
            <div className="flash-content">
                <p>{flash.message}</p>
                <button className="close-btn" onClick={() => setVisible(false)}>
                    <i className="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    );
}
