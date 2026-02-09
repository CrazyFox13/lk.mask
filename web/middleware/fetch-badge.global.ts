import {useAuthStore} from "~/stores/user";
import {apiFetch, defineNuxtRouteMiddleware} from "#imports";
import {useBadgeStore} from "~/stores/badges";

export default defineNuxtRouteMiddleware(async (to, from) => {
    if (process.server) return
    if (to.fullPath === '/_content/ws') return;
    const {user} = useAuthStore();
    if (!user) return;

    const {getBadges} = useBadgeStore();
    await getBadges();
});