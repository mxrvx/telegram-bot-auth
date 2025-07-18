import * as Ably from 'ably'

class AblyClient {
    apiKey: string
    clientId: string
    ably: Ably.Realtime
    channel: Ably.RealtimeChannel

    constructor(apiKey: string, clientId: string) {
        this.apiKey = apiKey
        this.clientId = clientId
        this.ably = new Ably.Realtime({
            key: apiKey,
            clientId: clientId,
        })
        this.channel = this.ably.channels.get(this.getChannelName())
    }

    getChannelName() {
        return `private:${$ns}:${this.clientId}`
    }
    async subscribeToPrivateChannel(onMessage: (data: any) => void) {
        await this.channel.attach()

        this.channel.subscribe((message: Ably.Types.Message) => {
            let parsedData = {}
            try {
                parsedData = JSON.parse(message.data)
            } catch {
                parsedData = message.data
            }
            if (onMessage) {
                onMessage(parsedData)
            }
        })
    }
}

export default AblyClient
