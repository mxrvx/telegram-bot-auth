const Xtype: string = $ns + '.module.auth'
const Cls: string = $cls(Xtype)
export const ApiPath: string = '/web/auth/'
import Session from '../../tools/session'
import Alpine from 'alpinejs'
export default function () {
    const storeData = Alpine.store(`${$config.namespace}_data`)
    const storeUser = Alpine.store(`${$config.namespace}_user`)

    function setCurrent(value) {
        return Session.setItem('current', value)
    }

    function getCurrent() {
        return Session.getItem('current')
    }

    return {
        data: storeData,
        user: storeUser,
        loading: true,
        current: false,
        init() {
            this.$root.classList.add(...Cls.split(' '))
            this.loading = false

            this.$watch('data.data', (newVal, oldVal) => {
                const current = getCurrent() === 'true' ? true : this.current

                if (current) {
                    if (newVal.redirect) {
                        window.location.href = newVal.redirect
                    }
                } else {
                    setTimeout(() => {
                        window.location.reload()
                    }, 1000)
                }
            })
        },
        login() {
            const win = window.open('', '_blank')
            if (!win) {
                alert($l('errors.window.open'))
                return
            }

            $post(`${ApiPath}login/`, {
                params: {},
            })
                .then((data) => {
                    if (data.url) {
                        this.current = true
                        setCurrent(this.current)
                        win.location.href = data.url
                    } else {
                        win.close()
                        alert($l('errors.action.link'))
                    }
                })
                .catch(() => {
                    win.close()
                    window.location.reload()
                })
        },
        logout() {
            const win = window.open('', '_blank')
            if (!win) {
                alert($l('errors.window.open'))
                return
            }

            $post(`${ApiPath}logout/`, {
                params: {},
            })
                .then((data) => {
                    if (data.url) {
                        this.current = true
                        setCurrent(this.current)
                        win.location.href = data.url
                    } else {
                        win.close()
                        alert($l('errors.action.link'))
                    }
                })
                .catch(() => {
                    win.close()
                    window.location.reload()
                })
        },
    }
}
