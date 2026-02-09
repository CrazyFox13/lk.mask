import {acceptHMRUpdate, defineStore} from "pinia";
import {apiFetch} from "#imports";

export const useBadgeStore = defineStore("badge", () => {

    const badges = ref({
        total_badges_value: 0,
        reports_badges_value: 0,
        unresolved_reports_badges_value: 0,
        to_me_reports_badges_value: 0,
        notifications_badges_value: 0,
        recommendations_badges_value: 0
    });

    const removeBadge = (type:any,count:number)=>{
        (badges.value as any)[type] =  (badges.value as any)[type] - count;
    }

    async function getBadges() {
        const {
            total_badges_value,
            reports_badges_value,
            unresolved_reports_badges_value,
            to_me_reports_badges_value,
            notifications_badges_value,
            recommendations_badges_value
        } = await apiFetch('badges/total', {}, true);

        badges.value = {
            total_badges_value: total_badges_value,
            reports_badges_value: reports_badges_value,
            unresolved_reports_badges_value: unresolved_reports_badges_value,
            to_me_reports_badges_value: to_me_reports_badges_value,
            notifications_badges_value: notifications_badges_value,
            recommendations_badges_value: recommendations_badges_value
        };
    }

    return {
        getBadges,
        badges,
        removeBadge
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useBadgeStore, import.meta.hot));
