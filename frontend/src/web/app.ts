export interface AppConfig {
    context?: string
    namespace?: string
    api_url?: string
    locale?: string
    store?: Record<string, any>
    lexicon?: Record<string, string>
    [key: string]: unknown
}

class App {
    private static instance: App | null = null
    public config: AppConfig = {}
    private constructor() {}
    public static getInstance(): App {
        if (!App.instance) {
            App.instance = new App()
        }
        return App.instance
    }
    public async loadConfig(configUrl: string, initialConfig?: AppConfig): Promise<void> {
        const fetchedConfig = (await $get(configUrl)) as Partial<AppConfig>
        if (!fetchedConfig) {
            throw new Error(`Failed to fetch config from ${configUrl}`)
        }
        const defaultConfig: AppConfig = {
            context: 'web',
            namespace: '',
            api_url: '',
            locale: 'en',
            store: {},
            lexicon: {},
        }
        this.config = {
            ...defaultConfig,
            ...initialConfig,
            ...fetchedConfig,
            store: {
                ...defaultConfig.store,
                ...(initialConfig?.store ?? {}),
                ...(fetchedConfig.store ?? {}),
            },
            lexicon: {
                ...defaultConfig.lexicon,
                ...(initialConfig?.lexicon ?? {}),
                ...(fetchedConfig.lexicon ?? {}),
            },
        }
    }

    public getConfig(): Readonly<AppConfig> {
        return this.config
    }
}

export default App
