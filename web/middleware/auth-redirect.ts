import {useCookie} from "#imports";

export default defineNuxtRouteMiddleware((to, from) => {
    const authRoutes = ['/auth/sign-up', '/auth/sign-in'];
    if (!from.path.includes("auth") && authRoutes.includes(to.path)) {
        const cookie = useCookie('redirect_to');
        cookie.value = from.path;
    }
})