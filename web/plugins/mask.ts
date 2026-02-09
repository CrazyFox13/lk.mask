import {vMaska} from "maska";
import {defineNuxtPlugin} from '#app'

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.directive('maska', vMaska);
});