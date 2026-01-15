import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // チャンクサイズの警告を無効化
        chunkSizeWarningLimit: 1000,
        // 圧縮の最適化（esbuildを使用、本番環境で高速）
        minify: 'esbuild',
        // ソースマップを無効化（本番環境でパフォーマンス向上）
        sourcemap: false,
        // アセットのインライン化しきい値を設定
        assetsInlineLimit: 4096,
        // コード分割はViteの自動分割に任せる（安全性を優先）
    },
});
