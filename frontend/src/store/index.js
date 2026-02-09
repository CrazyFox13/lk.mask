import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from "vuex-persistedstate";
import router from "@/router";
import Swal from "sweetalert2-khonik";

Vue.use(Vuex)

export default new Vuex.Store({
    plugins: [createPersistedState()],
    state: {
        user: null,
        token: null,

        attachedUser: undefined,
        detachedUser: undefined,

        menuBadges: {
            orders: 0,
            reports: 0,
            claims: 0,
            recommendations: 0,
            companies: 0,
        }

    },
    getters: {
        user: state => state.user,
        ordersOnModerationCount: state => state.menuBadges.orders,
        refereeReportsCount: state => state.menuBadges.reports,
        draftClaimsCount: state => state.menuBadges.claims,
        draftRecommendationsCount: state => state.menuBadges.recommendations,
        moderationCompaniesCount: state => state.menuBadges.companies,
    },
    mutations: {
        auth(state, token) {
            state.token = token;
            Vue.http.headers.common['Authorization'] = `Bearer ${state.token}`;
        },
        setUser(state, user) {
            state.user = user;
        },
        attachUserToNotification(state, user) {
            state.attachedUser = user;
        },
        detachUserFromNotification(state, user) {
            state.detachedUser = user;
        },
        setBadges(state, data) {
            state.menuBadges[data.type] = data.value;
        }
    },
    actions: {
        async GET_ME({commit}) {
            Vue.http.get(`auth/me`).then(r => {
                commit('setUser', r.body.user);
            }).catch(() => {
                commit('auth', undefined);
                commit('setUser', undefined);
                router.push('/login')
            })
        },
        FETCH_BADGES({commit}) {
            Vue.http.get(`badges/admin/orders`).then(r => {
                commit('setBadges', {
                    type: 'orders',
                    value: r.body.moderation_orders_count
                })
            });
            Vue.http.get(`badges/admin/reports`).then(r => {
                commit('setBadges', {
                    type: 'reports',
                    value: r.body.referee_reports_count
                })
            });
            Vue.http.get(`badges/admin/claims`).then(r => {
                commit('setBadges', {
                    type: 'claims',
                    value: r.body.draft_claims_count
                })
            });
            Vue.http.get(`badges/admin/recommendations`).then(r => {
                commit('setBadges', {
                    type: 'recommendations',
                    value: r.body.draft_recommendations_count
                })
            });
            Vue.http.get(`badges/admin/companies`).then(r => {
                commit('setBadges', {
                    type: 'companies',
                    value: r.body.moderation_companies_count
                })
            });
        },

        async notificationSend(ctx, notification) {
            return new Promise(resolve => {
                Swal.fire({
                    title: "Подтверждаете отправку рассылки?",
                    showDenyButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Отменить',
                    showCloseButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Подтверждаю'
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        Vue.http.post(`push-notifications/${notification.id}/send`).then(({body}) => {
                            resolve(body.pushNotification);
                        })
                    }
                });
            })
        },
        async notificationPause(ctx, notification) {
            return new Promise(resolve => {
                Swal.fire({
                    title: "Остановить рассылку?",
                    showDenyButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Отменить',
                    showCloseButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Подтверждаю'
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        Vue.http.post(`push-notifications/${notification.id}/pause`).then(({body}) => {
                            resolve(body.pushNotification);
                        })
                    }
                });
            })

        },
        async notificationResume(ctx, notification) {
            return new Promise(resolve => {
                Swal.fire({
                    title: "Возобновить рассылку?",
                    showDenyButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Отменить',
                    showCloseButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Подтверждаю'
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        Vue.http.post(`push-notifications/${notification.id}/resume`).then(({body}) => {
                            resolve(body.pushNotification);
                        })
                    }
                });
            })
        },
        async notificationCancel(ctx, notification) {
            return new Promise(resolve => {
                Swal.fire({
                    title: "Отменить рассылку?",
                    showDenyButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Отменить',
                    showCloseButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Подтверждаю'
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        Vue.http.post(`push-notifications/${notification.id}/cancel`).then(({body}) => {
                            resolve(body.pushNotification)
                        })
                    }
                });
            })
        },
        async notificationCopy(ctx, {notification, ignoreSent}) {
            return new Promise(resolve => {
                Vue.http.post(`push-notifications/${notification.id}/copy?ignore_sent=${ignoreSent ? 1 : 0}`).then(({body}) => {
                    resolve(body.pushNotification)
                })
            })
        }
    },
    modules: {}
})
