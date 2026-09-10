import { defineConfig, searchForWorkspaceRoot } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        fs: {
            allow: [
                searchForWorkspaceRoot(process.cwd()),
                fs.realpathSync(process.cwd()),
            ],
        },
    },
    plugins: [
        laravel({
            input: ['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'],
            refresh: true,
        }),
    ],
});
