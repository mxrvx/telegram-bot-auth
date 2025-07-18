import Alpine from 'alpinejs'
import AppInstance from '../../app'
import Auth, {ApiPath as AuthApiPath} from '../auth/index'
import AblyClient from '../ably/index'

interface UserStore {
    data: Record<string, any>
    init(): void
    setData(data: any): void
    setValue(key: string, value: any): void
}

interface TaskStore {
    data: any
    ablyClient: AblyClient | null
    init(): void
    getClientId(): Promise<string>
}

async function bootstrap() {
    const App = AppInstance.getInstance()
    await App.loadConfig(`${AuthApiPath}config/`, $config)

    const userData = (App.config.store as Record<string, any>)?.user ?? {}
    const taskData = (App.config.store as Record<string, any>)?.task ?? {}

    const store: {user: UserStore; data: TaskStore} = {
        user: {
            data: userData,
            init() {},
            setData(data) {
                this.data = data
            },
            setValue(key: string, value: any) {
                if (this.data && typeof this.data === 'object') {
                    this.data = {...this.data, [key]: value}
                }
            },
        },
        data: {
            data: {},
            ablyClient: null as AblyClient | null,
            async init() {
                try {
                    const clientId = await this.getClientId()
                    this.ablyClient = new AblyClient(`${App.config.ably_api_key}`, clientId)
                    this.ablyClient.subscribeToPrivateChannel((data) => {
                        this.data = data
                    })
                } catch (error) {
                    console.error('Initialization error AblyClient:', error)
                }
            },
            async getClientId(): Promise<string> {
                const clientId = await taskData.uuid
                if (clientId) {
                    return clientId
                }
                throw new Error('Error clientId')
            },
        },
    }

    const modules = {auth: Auth}

    Object.entries(modules).forEach(([name, moduleFn]) => {
        Alpine.data(`${$config.namespace}_${name}`, moduleFn)
    })

    Object.entries(store).forEach(([name, storeObj]) => {
        Alpine.store(`${$config.namespace}_${name}`, storeObj)
    })

    Alpine.start()
}

bootstrap()
