// @ts-ignore
import path from 'path'
import {mergeConfig} from 'vite'
import ai from 'unplugin-auto-import/vite'
import eslint from 'vite-plugin-eslint'
export const aiConfig = {
    dts: true,
    include: [/\.[tj]sx?$/, /\.vue$/, /\.vue\?vue/],
    imports: [
        {
            '@/constants': ['NAMESPACE', ['NAMESPACE', '$ns'], 'CONFIG', ['CONFIG', '$config']],
        },
        {
            '@/tools/cmp': ['getClsByXtype', ['getClsByXtype', '$cls']],
        },
        {
            '@/tools/lexicon': ['useLexicon', ['useLexicon', '$l']],
        },
        {
            '@/tools/api': [
                'apiRequest',
                ['apiRequest', '$api'],
                'getRequest',
                ['getRequest', '$get'],
                'postRequest',
                ['postRequest', '$post'],
                'putRequest',
                ['putRequest', '$put'],
                'patchRequest',
                ['patchRequest', '$patch'],
                'deleteRequest',
                ['deleteRequest', '$delete'],
            ],
        },
    ],
}

export default function withNamespace(namespace: string, config = {}) {
    if (!namespace) {
        throw new Error('You should specify a Package namespace, something like `mxrvx-app`')
    }

    // @ts-ignore
    return mergeConfig(
        {
            define: {
                'import.meta.env.NAMESPACE': JSON.stringify(namespace),
            },
            resolve: {
                alias: {
                    '@': path.resolve(__dirname),
                },
            },
            plugins: [eslint(), ai(aiConfig)],
            server: {
                host: '0.0.0.0',
                port: 9090,
                strictPort: true,
            },
            base: '/assets/components/' + namespace + '/',
        },
        config,
    )
}
