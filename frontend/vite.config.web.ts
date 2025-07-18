// @ts-ignore
import fs from 'fs'
// @ts-ignore
import path from 'path'
import ai from 'unplugin-auto-import/vite'
import withNamespace, {aiConfig} from './src/web/config'
import {terser} from 'rollup-plugin-terser'

const alpinePackagePath = path.resolve(__dirname, 'node_modules/alpinejs/package.json')
const alpinePackageJson = JSON.parse(fs.readFileSync(alpinePackagePath, 'utf-8'))
const alpineVersionFull = alpinePackageJson.version

export default withNamespace('mxrvx-telegram-bot-auth', {
    esbuild: {
        target: 'node18',
    },
    // plugins: [],
    plugins: [
        ai({
            ...aiConfig,
            dirs: ['src/web/tools'],
        }),
    ],
    build: {
        manifest: 'manifest.json',
        emptyOutDir: true,
        //sourcemap: true,
        outDir: path.resolve(__dirname, '../assets/src/web'),
        assetsDir: '',
        rollupOptions: {
            plugins: [terser()],
            input: './src/web.js',
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/alpinejs')) {
                        return 'alpinejs'
                    }
                },
                chunkFileNames(chunkInfo) {
                    if (chunkInfo.name === 'alpinejs') {
                        return `alpinejs@${alpineVersionFull}.min.js`
                    }
                    return 'chunks/[name]-[hash].js'
                },
                entryFileNames: '[name]-[hash].js',
            },
        },
        minify: 'terser',
        terserOptions: {
            format: {
                comments: false,
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
            },
        },
    },
})
