import {abortNavigation, defineNuxtRouteMiddleware, navigateTo} from "#imports";
import type {RouteLocationNormalized} from "vue-router";

export default defineNuxtRouteMiddleware((to: RouteLocationNormalized) => {
    const path = to.path;
    if (/[A-Z]/.test(path)) {
        return navigateTo(path.toLowerCase(), { redirectCode: 301 });
    }
});
