// https://v3.nuxtjs.org/docs/directory-structure/nuxt.config

// @ts-ignore
import { defineNuxtConfig } from "nuxt/config";

export default defineNuxtConfig({
    app: {
        // head
        head: {
            title: 'Поиск спецтехники на бирже ASTT.SU - агрегатор аренды и заказа спецтехники по всей России, подбор техники на сайте',
            meta: [
                //<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
                { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=1' },
                {
                    hid: 'description',
                    name: 'description',
                    content: 'Услуги по заказу и аренде спецтехники. Легкий поиск и подбор техники через агрегатор (биржу). Подайте заявку на спецтехнику или найдите подходящие услуги в одном сайте.',
                },
                /* {
                     hid: 'keywords',
                     name: 'keywords',
                     content: '',
                 },

                 */
                {
                    hid: 'og:locale',
                    name: 'og:locale',
                    content: 'ru_RU',
                },
                {
                    hid: 'og:image',
                    name: 'og:image',
                    content: 'https://storage.yandexcloud.net/astt-su/0_service/astt_logo.png',
                },


            ],
            link: [{ rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }],

            script: [
                {
                    hid: 'top-mail-ru',
                    src: 'https://top-fwz1.mail.ru/js/code.js',
                    async: true,
                    body: true
                },
                {
                    hid: 'yandex-ads',
                    src: 'https://yandex.ru/ads/system/context.js',
                    async: true,
                    body: true,
                },

                {
                    hid: "yc-captcha",
                    src: "https://smartcaptcha.yandexcloud.net/captcha.js",
                    async: true,
                    body: true,
                }
            ],
            noscript: [
                {
                    innerHTML: '<div><img src="https://top-fwz1.mail.ru/counter?id=3552318;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div>',
                    body: true
                }
            ],
            // __dangerouslyDisableSanitizers: ['script', 'noscript']
        }
    },

    // css
    css: ['~/assets/scss/index.scss'],

    typescript: {
        strict: true,
        shim: false,
    },

    // build modules
    modules: [
        '@vueuse/nuxt',
        '@unocss/nuxt',
        '@pinia/nuxt',
        '@element-plus/nuxt',
        '@nuxt/image',
        '@nuxtjs/sitemap',
        '@nuxtjs/robots',
        [
            'nuxt-delay-hydration',
            {
                mode: 'mount'
            }
        ],
        [
            'yandex-metrika-module-nuxt3',
            {
                id: process.env.YANDEX_METRIKA_ID,
                webvisor: true,
                // consoleLog: true,
                clickmap: true,
                // useCDN: false,
                trackLinks: true,
                accurateTrackBounce: true,
                trackHash: true,
            }
        ]
    ],

    sitemap: {
        hostname: process.env.BASE_URL,
        exclude: [
            '/profile/**',
            '/companies/**'
        ],
        sources: [
            `${process.env.BASE_URL}/api/seo/public-urls`,
        ],
    },
    robots: {
        /* module options */
        rules: {
            //    UserAgent: '*',
            //   Disallow: ''
        }
    },

    // vueuse
    vueuse: {
        ssrHandlers: true,
    },

    unocss: {
        uno: true,
        attributify: true,
        icons: {
            scale: 1.2,
        },
    },

    elementPlus: {
        icon: 'ElIcon',
        //  locale:ru,
    },

    runtimeConfig: {
        public: {
            baseURL: (process.env.BASE_URL || 'http://localhost:5000').replace(/\/$/, ''),
            pusherAppKey: process.env.PUSHER_APP_KEY,
            pusherCluster: process.env.PUSHER_APP_CLUSTER,
            pusherHost: process.env.PUSHER_APP_HOST,
            pusherSchema: process.env.PUSHER_APP_SCHEMA,
            pusherPort: process.env.PUSHER_APP_PORT,
        },
    },

    routeRules: {
        '/3g6a': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=spectehnikamsk1&utm_content=invite' },
        '/app': { redirect: 'https://astt.su/download-app?utm_source=newsletter&utm_medium=messenger&utm_campaign=supplier' },
        '/apps': { redirect: 'https://astt.su/download-app?utm_source=newsletter&utm_medium=sms&utm_campaign=one' },
        '/ast-plyus': { redirect: 'https://astt.su/download-app?utm_source=referral&utm_medium=astplyus' },
        '/d3hd': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=zakazmos&utm_content=invite' },
        '/d5uz': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=spetctechnika_arenda_uslugi&utm_content=invite' },
        '/df3z': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=free&utm_content=freebie' },
        '/dfh2': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=rabotajcbMos&utm_content=invite://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=free&utm_content=freebie' },
        '/dg3a': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=arenda_spetstechniki_spb&utm_content=invite' },
        '/download': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=official_channel' },
        '/hjas': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=Arendakompressorov777&utm_content=invite' },
        '/impuls': { redirect: 'https://astt.su/download-app?utm_source=referral&utm_medium=impuls' },
        '/j34s': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=free&utm_content=update' },
        '/r3d5': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=free&utm_content=invite' },
        '/u76z': { redirect: 'https://astt.su/download-app?utm_source=telegram&utm_medium=post&utm_campaign=samosvalmoszakaz&utm_content=invite' },
        '/welcome': { redirect: 'https://astt.su/download-app?utm_source=newsletter&utm_medium=messenger&utm_campaign=client' },
    },

    compatibilityDate: '2024-09-09',
});