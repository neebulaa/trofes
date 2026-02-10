import "./bootstrap";
import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";

import "../css/init.css";

createInertiaApp({
    resolve: (name) => {
        // const pages = import.meta.glob("./Pages/**/*.jsx", { eager: true });
        // return pages[`./Pages/${name}.jsx`];
        const pages = import.meta.glob("./Pages/**/*.jsx");
        return pages[`./Pages/${name}.jsx`]()
            .then(module => module);
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
        const splash = document.getElementById("loading-screen");
        if (splash) {
            splash.style.transition = "opacity 200ms ease";
            setTimeout(() => {
                splash.style.opacity = "0";
                setTimeout(() => {
                    splash.remove()
                }, 200)
            }, 2000);
            // splash.remove();
        }
    },
});
