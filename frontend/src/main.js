import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router'
import store from './store'
import VueResource from 'vue-resource';
import VueMask from 'v-mask'
import ResourceStatuses from "@/mixins/ResourceStatuses";
import QueryHelper from "@/mixins/QueryHelper";

Vue.config.productionTip = false
Vue.use(VueMask);
Vue.use(VueResource);
Vue.http.options.root = `${process.env.VUE_APP_API_ENDPOINT}/api`;
Vue.http.headers.common['Authorization'] = `Bearer ${store.state.token}`;

Vue.mixin(ResourceStatuses);
Vue.mixin(QueryHelper);
Vue.mixin({
    methods: {
        copyObject(object) {
            return JSON.parse(JSON.stringify(object))
        }
    }
})

new Vue({
    vuetify,
    router,
    store,
    render: h => h(App)
}).$mount('#app')
