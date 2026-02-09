import {acceptHMRUpdate, defineStore} from "pinia";
import {onMounted} from "#imports";

export const useDeviceStore = defineStore("device", () => {

    const isMobile = ref(true);

    const androidLink = "https://play.google.com/store/apps/details?id=su.astt&referrer=utm_source%3Dsait";
    const iosLink = "https://apps.apple.com/app/apple-store/id6443506880?pt=122133554&ct=Sait&mt=8";

    onMounted(() => {
        checkIfMobile();

        window.addEventListener('resize', checkIfMobile)
    });

    function checkIfMobile() {
        isMobile.value = window.innerWidth < 992;
    }

    return {
        isMobile,
        iosLink,
        androidLink
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useDeviceStore, import.meta.hot));
