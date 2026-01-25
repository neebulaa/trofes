import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            // input: ['resources/css/app.css', 'resources/js/app.js'],
            input: ["resources/js/app.jsx"],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    server: {
        host: true,
        port: 5173,
        strictPort: true,
        hmr: {
            // host: "174.139.118.71",
            host: "174.139.115.31",
            port: 5173,
        },
    },
});
