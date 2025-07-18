export function useConfig(key: string) {
    // @ts-ignore
    const config = window[$ns]?.config || {}

    return key in config ? config[key] : undefined
}
