import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

const zipDownloads = {
  name: 'food-release-zip-download-headers',
  configurePreviewServer(server) {
    const files = new Set([
      '/ticket-update-1.62.2-food-dashboard-bulk-theme-fixes-20261025.zip',
      '/ticket-update-1.62.3-food-staff-report-theme-20261025.zip',
      '/food-update-1.62.3-download.zip',
    ]);
    server.middlewares.use((req, res, next) => {
      const pathname = (req.url || '').split('?')[0];
      if (pathname === '/download-food-update-1.62.3' || pathname === '/download-1.62.3-v2') {
        const filename = pathname === '/download-1.62.3-v2'
          ? 'food-update-1.62.3-download.zip'
          : 'ticket-update-1.62.3-food-staff-report-theme-20261025.zip';
        const body = readFileSync(resolve(server.config.root, 'dist', filename));
        res.statusCode = 200;
        res.setHeader('Content-Type', 'application/zip');
        res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
        res.setHeader('Content-Length', String(body.length));
        res.setHeader('Cache-Control', 'no-store, max-age=0');
        res.setHeader('X-Content-Type-Options', 'nosniff');
        if (req.method === 'HEAD') res.end();
        else res.end(body);
        return;
      }
      if (files.has(pathname)) {
        res.setHeader('Content-Type', 'application/zip');
        res.setHeader('Content-Disposition', `attachment; filename="${pathname.slice(1)}"`);
        res.setHeader('X-Content-Type-Options', 'nosniff');
      }
      next();
    });
  },
};

export default defineConfig({
  plugins: [zipDownloads],
  base: '/',
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    allowedHosts: true,
  },
  preview: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    allowedHosts: true,
    headers: { 'Cache-Control': 'no-store, max-age=0' },
  },
});
