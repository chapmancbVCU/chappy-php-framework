import fs from 'node:fs';
import path from 'node:path';

/**
 * Vite plugin that writes the dev server's public URL to a "hot" file
 * while `npm run dev` is running, and removes it on exit.
 *
 * Chappy.php reads this file (Core\Lib\React\Vite::devServerUrl()) to
 * decide between dev server assets and the build manifest, so the
 * dev server URL never has to be hardcoded on the PHP side.
 *
 * On `vite build`, any stale hot file is removed so the app falls back to
 * the build manifest.
 *
 * URL resolution order:
 *   1. options.url
 *   2. server.origin from vite.config.js
 *   3. protocol + server.hmr.host (or the bound address) + port
 *
 * @param {object}  [options]
 * @param {string}  [options.hotFile='public/hot'] Path relative to the project root.
 * @param {string}  [options.url] Explicit public URL of the dev server.
 * @returns {import('vite').Plugin}
 */
export default function chappyHot(options = {}) {
    let hotFile;

    const removeHotFile = () => {
        if (hotFile && fs.existsSync(hotFile)) {
            fs.rmSync(hotFile);
        }
    };

    return {
        name: 'chappy-hot',

        configResolved(config) {
            hotFile = path.resolve(config.root, options.hotFile ?? 'public/hot');

            // A build means "use the manifest".  Remove a hot file left behind
            // by a dev server that was killed without cleaning up.
            if (config.command === 'build') {
                removeHotFile();
            }
        },

        configureServer(server) {
            server.httpServer?.once('listening', () => {
                const address = server.httpServer.address();
                const cfg = server.config.server;
                const protocol = cfg.https ? 'https' : 'http';

                let url = options.url ?? cfg.origin;

                if (!url && address && typeof address === 'object') {
                    const hmrHost = typeof cfg.hmr === 'object' ? cfg.hmr.host : undefined;
                    let host = hmrHost ?? address.address;

                    // Wildcard binds are not reachable as-is from a browser.
                    if (['0.0.0.0', '::', '::1', '127.0.0.1'].includes(host)) {
                        host = 'localhost';
                    }
                    if (host.includes(':')) {
                        host = `[${host}]`; // bare IPv6
                    }
                    url = `${protocol}://${host}:${address.port}`;
                }

                fs.mkdirSync(path.dirname(hotFile), { recursive: true });
                fs.writeFileSync(hotFile, url);
                server.config.logger.info(`  chappy-hot: wrote ${url} to ${path.relative(server.config.root, hotFile)}`);
            });

            server.httpServer?.once('close', removeHotFile);
            process.once('exit', removeHotFile);
            for (const signal of ['SIGINT', 'SIGTERM', 'SIGHUP']) {
                process.once(signal, () => {
                    removeHotFile();
                    process.exit();
                });
            }
        },
    };
}
