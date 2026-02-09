import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {defineNuxtRouteMiddleware, navigateTo} from "#imports";
import type {RouteLocationNormalized} from "vue-router";

export default defineNuxtRouteMiddleware((to: RouteLocationNormalized, from: RouteLocationNormalized) => {
    const {user} = storeToRefs(useAuthStore());
    if (!user.value) {
        console.log("force to login")
        return navigateTo('/auth/sign-in')
    }
    if (user.value.temp_password && to.name !== "auth-set-password") {
        console.log('force-to set password')
        return navigateTo('/auth/set-password')
    }
})