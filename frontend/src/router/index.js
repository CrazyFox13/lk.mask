import Vue from 'vue'
import VueRouter from 'vue-router'
import HomeView from '../views/HomeView.vue'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomeView
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/LoginView.vue')
    },
    {
        path: '/regions',
        name: 'regions',
        component: () => import('../views/Regions.vue')
    },
    {
        path: '/regions/:region_id/cities',
        name: 'cities',
        component: () => import('../views/Cities.vue')
    },
    {
        path: '/companies',
        name: 'companies',
        component: () => import('../views/CompaniesView.vue')
    },
    {
        path: '/companies/create',
        name: 'companyCreate',
        component: () => import('../views/Company/CompanyCreate.vue')
    },

    {
        path: '/companies/:id',
        name: 'company',
        component: () => import('../views/Company/CompanyView.vue')
    },
    {
        path: '/companies/:id/photo-groups/:groupId',
        name: 'photoGroup',
        component: () => import('../views/Company/PhotosView.vue')
    },
    {
        path: '/company-types',
        name: 'company-types',
        component: () => import('../views/CompanyTypesView.vue')
    },
    {
        path: '/moderators',
        name: 'moderators',
        component: () => import('../views/ModeratorsView.vue')
    },
    {
        path: '/orders',
        name: 'orders',
        component: () => import('../views/OrdersView.vue')
    },
    {
        path: '/orders/:id',
        name: 'order',
        component: () => import('../views/Order/OrderView.vue')
    },
    {
        path: '/push-notifications',
        name: 'push-notifications',
        component: () => import('../views/PushNotificationsView.vue')
    },
    {
        path: '/push-notifications/create',
        name: 'push-notifications/create',
        component: () => import('../views/PushNotification/PushNotificationСreate.vue')
    },
    {
        path: '/push-notifications/:id',
        name: 'push-notification',
        component: () => import('../views/PushNotificationItem.vue')
    },
    {
        path: '/push-notifications/:id/edit',
        name: 'push-notification/edit',
        component: () => import('../views/PushNotification/PushNotificationEdit.vue')
    },
    {
        path: '/email-notifications',
        name: 'email-notifications',
        component: () => import('../views/EmailNotificationsView.vue')
    },
    {
        path: '/email-notifications/:id',
        name: 'email-notification',
        component: () => import('../views/EmailNotificationItem.vue')
    },
    {
        path: '/email-notification-templates',
        name: 'email-notification-templates',
        component: () => import('../views/EmailNotificationTemplatesView.vue')
    },

    {
        path: '/recommendations',
        name: 'recommendations',
        component: () => import('../views/RecommendationsView.vue')
    },
    {
        path: '/claims',
        name: 'claims',
        component: () => import('../views/ClaimsView.vue')
    },
    {
        path: '/report-types',
        name: 'reportTypes',
        component: () => import('../views/ReportTypesView.vue')
    },
    {
        path: '/reports',
        name: 'reports',
        component: () => import('../views/ReportsView.vue')
    },

    {
        path: '/reports/:id',
        name: 'report',
        component: () => import('../views/Report/ReportView.vue')
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('../views/UsersView.vue')
    },
    {
        path: '/users/:id',
        name: 'user',
        component: () => import('../views/Users/UserView.vue')
    },

    {
        path: '/vehicle-categories',
        name: 'vehicle-categories',
        component: () => import('../views/VehicleCategoriesView.vue')
    },
    {
        path: '/vehicle-categories/:id',
        name: 'vehicle-category',
        component: () => import('../views/Vehicle/VehicleCategoryView.vue')
    },
    {
        path: '/vehicles',
        name: 'vehicles',
        component: () => import('../views/VehiclesView.vue')
    },
    {
        path: '/vehicle-groups/:id',
        name: 'vehicle',
        component: () => import('../views/Vehicle/VehicleGroupView.vue')
    },
    {
        path: '/vehicle-groups/:id/vehicle-types/:type_id',
        name: 'vehicle-type',
        component: () => import('../views/Vehicle/VehicleTypeView.vue')
    },

    {
        path: '/profile',
        name: 'profile',
        component: () => import('../views/ProfileView.vue')
    },
    {
        path: '/reserved-numbers',
        name: 'reserved-numbers',
        component: () => import('../views/ReservedNumbersView.vue')
    },
    {
        path: '/awards',
        name: 'awards',
        component: () => import('../views/AwardsView.vue')
    },
    {
        path: '/payment-units',
        name: 'paymentUnits',
        component: () => import('../views/PaymentUnitsView.vue')
    },
    {
        path: '/faq',
        name: 'faq',
        component: () => import('../views/FAQ.vue')
    },
    {
        path: '/faq/create',
        name: 'faqCreate',
        component: () => import('../views/Page/FAQCreate.vue')
    },
    {
        path: '/materials',
        name: 'materials',
        component: () => import('../views/MaterialsView.vue')
    },
    {
        path: '/materials/create',
        name: 'materialsCreate',
        component: () => import('../views/Page/MaterialCreate.vue')
    },
    {
        path: '/pages/:id',
        name: 'pageEdit',
        component: () => import('../views/Page/PageEdit.vue')
    },
    {
        path: '/advertisers',
        name: 'advertisers',
        component: () => import('../views/AdvertisersView.vue')
    },
    {
        path: '/adv-banners',
        name: 'advBanners',
        component: () => import('../views/AdvBannersView.vue')
    },
    {
        path: '/adv-reports',
        name: 'advReport',
        component: () => import('../views/AdvReportView.vue')
    },
    {
        path: '/orders-report',
        name: 'orders-report',
        component: () => import('../views/AdminReportOrders.vue')
    },

]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

export default router
