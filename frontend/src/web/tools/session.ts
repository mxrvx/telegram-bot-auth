const Session = {
    ns: $ns,

    setNamespace(namespace: string) {
        this.ns = namespace
    },

    setItem(key: string, value: any) {
        if (!this.ns) throw new Error('Namespace is not set in StorageHelper.')
        sessionStorage.setItem(`${this.ns}.${key}`, String(value))
    },

    getItem(key: string): string | null {
        if (!this.ns) throw new Error('Namespace is not set in StorageHelper.')
        return sessionStorage.getItem(`${this.ns}.${key}`)
    },

    removeItem(key: string) {
        if (!this.ns) throw new Error('Namespace is not set in StorageHelper.')
        sessionStorage.removeItem(`${this.ns}.${key}`)
    },
}

export default Session
