import { defineConfig, searchForWorkspaceRoot } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

const realCwd = fs.realpathSync(process.cwd());
if (process.cwd() !== realCwd) {
    try { process.chdir(realCwd); } catch (e) {}
}

export default defineConfig({
    root: realCwd,
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        fs: {
            allow: [
                searchForWorkspaceRoot(realCwd),
                realCwd,
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