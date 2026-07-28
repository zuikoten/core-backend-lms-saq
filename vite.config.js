import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { globSync } from "glob";

const moduleScripts = globSync("resources/js/modules/**/*.js");

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                ...moduleScripts,
            ],
            refresh: true,
        }),
    ],
});