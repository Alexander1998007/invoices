import type { UseFetchOptions } from '#app'
import { useRuntimeConfig } from '#imports'

export const useApiBase = () => {
    const config = useRuntimeConfig()
    return import.meta.server
        ? (config.apiBaseInternal || config.public.apiBase)
        : config.public.apiBase
}

export const useApiFetch = <T>(url: string | Ref<string> | (() => string), options: UseFetchOptions<T> = {}) => {
    return useFetch(url, {
        baseURL: useApiBase(),
        ...options,
    })
}

export const $api = <T>(request: string, options: Parameters<typeof $fetch>[1] = {}) => {
    return $fetch<T>(request, {
        baseURL: useApiBase(),
        ...options,
    })
}
