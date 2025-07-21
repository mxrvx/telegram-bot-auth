import Web from './web/app.ts'
import './web/scss/index.scss'

declare global {
    interface Window {
        mxrvxTelegramBotAuth: Web
    }
}

// @ts-ignore
window.mxrvxTelegramBotAuth = Web.getInstance($config)
import.meta.glob('./web/components/inject/*.{js,ts}', {eager: true})
