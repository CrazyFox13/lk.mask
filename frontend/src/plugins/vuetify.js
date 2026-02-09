import Vue from 'vue';
import Vuetify from 'vuetify/lib/framework';

Vue.use(Vuetify);

export default new Vuetify({
    theme: {
        themes: {
            light: {
                primary: '#EB8A00',
                secondary: '#00667B',
                accent: '#0167DE',
                error: '#F92609',
            },
        },
    },
});
