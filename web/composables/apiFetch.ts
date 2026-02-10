import {useCookie, useRuntimeConfig} from "#imports";
import {useAuthStore} from "~/stores/user";

class ApiError extends Error {
    body: any
    status: number | undefined

    constructor(msg: string, body: any, status: number | undefined) {
        super(msg);
        this.body = body;
        this.status = status;
    }
}

export const apiFetch = async (url: string, opts?: any | undefined | null, server: boolean = true): Promise<any> => {
    const config = useRuntimeConfig();
    const {getToken} = useAuthStore();
    // В браузере — относительный путь (тот же хост, что и сайт). Сервер не трогаем.
    const base = typeof window !== 'undefined' ? '' : (config.public.baseURL || 'http://localhost:5000').replace(/\/$/, '');
    const finalUrl = base ? `${base}/api/${url}` : `/api/${url}`;
    const userSession = useCookie('user-session')
    const options = {
        ...(opts ? opts : {}),
        server: server,
        onResponseError({request, response, options}: any) {
            throw new ApiError('API ERROR', response._data, response.status);
        },
        onRequest({request, options}: any) {
            const headers: Record<string, string> = {
                ...(options.headers || {}),
                Authorization: `Bearer ${getToken()}`,
                Accept: 'application/json',
                UserSession: userSession.value,
            };
            if (options.body !== undefined && options.body !== null && typeof options.body === 'object' && !(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }
            options.headers = headers;
        },
    };
    return await $fetch(finalUrl, options);
};