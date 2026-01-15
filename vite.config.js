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
        // コード分割の最適化
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // node_modulesの依存関係をvendorチャンクに分離
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('@inertiajs')) {
                            return 'vendor-vue';
                        }
                        if (id.includes('ziggy')) {
                            return 'vendor-ziggy';
                        }
                        return 'vendor';
                    }
                },
            },
        },
        // チャンクサイズの警告を無効化（最適化により警告が出る可能性があるため）
        chunkSizeWarningLimit: 1000,
        // 圧縮の最適化（esbuildを使用、本番環境で高速）
        minify: 'esbuild',
        // ソースマップを無効化（本番環境でパフォーマンス向上）
        sourcemap: false,
        // アセットのインライン化しきい値を設定
        assetsInlineLimit: 4096,
    },
});
