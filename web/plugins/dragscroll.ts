import { defineNuxtPlugin } from '#imports';
import { dragscroll } from 'vue-dragscroll';

export default defineNuxtPlugin((nuxtApp: { vueApp: { directive: (arg0: string, arg1: any) => void; }; }) => {
    nuxtApp.vueApp.directive('dragscroll', dragscroll);
});
