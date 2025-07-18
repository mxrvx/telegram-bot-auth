interface ApiRequestConfig {
    endpoint: string
    method?: string
    params?: object
    headers?: object
    listeners?: object

    [key: string]: any
}

export function apiRequest(config: ApiRequestConfig): Promise<any> | any {
    const {endpoint, method = 'GET', params = {}, headers = {}, listeners = {}, handler, ...requestOptions} = config

    const context = $config.context || 'web'

    return new Promise(async (resolve, reject) => {
        try {
            const body =
                params instanceof FormData
                    ? (() => {
                          params.append('context', JSON.stringify(context))
                          return params
                      })()
                    : (() => {
                          const fd = new FormData()
                          for (const key in params) {
                              fd.append(key, params[key])
                          }
                          fd.append('context', JSON.stringify(context))
                          return fd
                      })()

            let url = `${$config.api_url}${endpoint}`.replace(/\/{2,}/g, '/')
            const isGetOrHead = method.toUpperCase() === 'GET' || method.toUpperCase() === 'HEAD'

            const fetchOptions: RequestInit = {
                method: method.toUpperCase(),
                headers: {
                    ...headers,
                },
                ...requestOptions,
            }

            if (!isGetOrHead) {
                fetchOptions.body = body
            } else {
                const queryParams = {
                    ...params,
                    context,
                }

                const queryString = new URLSearchParams()
                for (const key in queryParams) {
                    const value = queryParams[key]
                    queryString.append(key, typeof value === 'object' ? JSON.stringify(value) : String(value))
                }

                if (queryString.toString()) {
                    url += (url.includes('?') ? '&' : '?') + queryString.toString()
                }
            }

            const response = await fetch(url, fetchOptions)

            const text = await response.text()

            let data
            try {
                data = JSON.parse(text)
            } catch {
                data = text
            }

            if (response.ok) {
                resolve(data)
            } else {
                reject(data)
            }
        } catch (error) {
            reject(error)
        }
    })
}

export function getRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        ...config,
        endpoint,
        method: 'GET',
    })
}

export function postRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        ...config,
        endpoint,
        method: 'POST',
    })
}

export function putRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        ...config,
        endpoint,
        method: 'PUT',
    })
}

export function patchRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        ...config,
        endpoint,
        method: 'PATCH',
    })
}

export function deleteRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        ...config,
        endpoint,
        method: 'DELETE',
    })
}
