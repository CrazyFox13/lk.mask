import plugin from 'vue-yandex-maps'
import { defineNuxtPlugin } from 'nuxt/app'

const settings = {
    // здесь ваши настройки
    apiKey: 'f19aae6b-a296-423e-ad15-1e293f6e5379', // Индивидуальный ключ API
    lang: 'ru_RU', // Используемый язык
    coordorder: 'latlong', // Порядок задания географических координат
    debug: true, // Режим отладки
    version: '2.1' // Версия Я.Карт
}

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.use(plugin, settings)
})