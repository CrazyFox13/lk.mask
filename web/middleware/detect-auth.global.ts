import {useAuthStore} from "~/stores/user";
import {defineNuxtRouteMiddleware, useCookie} from "#imports";
import {storeToRefs} from "pinia";

export default defineNuxtRouteMiddleware(async (to, from) => {
    if (to.fullPath === '/_content/ws') return;
    const sessionCookie = useCookie('user-session', {});
    if (!sessionCookie.value) {
        sessionCookie.value = Date.now().toString()
    }
    const authStore = useAuthStore();
    const {getUser, authByHash} = authStore;
    const {user, jwt} = storeToRefs(authStore);
    const autoAuthHash = from.query?.hash;
    const email = from.query?.email;
    if (autoAuthHash && email) {
        await authByHash(decodeURIComponent(<string>autoAuthHash), decodeURIComponent(<string>email))
    }

    if (jwt.value && !user.value) {
        await getUser();
    }
});