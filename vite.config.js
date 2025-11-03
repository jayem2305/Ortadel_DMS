import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
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
        tailwindcss({
            extend: {
                fontFamily: {
                    lexend: ['Lexend', 'sans-serif'],
                },
                fontSize: {
                    'heading-lg': ['30px', { lineHeight: '120%', fontWeight: '700' }],
                },
                }
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
  proxy: {
    "/api": {
      target: "http://127.0.0.1:5001",
      changeOrigin: true,
      rewrite: (path) => path.replace(/^\/api/, "/api"), // could just be identity: path => path
    },
    "/files": {
      target: "http://127.0.0.1:5001",
      changeOrigin: true,
      rewrite: (path) => path.replace(/^\/files/, "/files"),
    },
  }
}

});
