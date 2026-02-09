import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {defineNuxtRouteMiddleware, navigateTo} from "#imports";
import type {RouteLocationNormalized} from "vue-router";

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

export default defineNuxtRouteMiddleware((to: RouteLocationNormalized, from: RouteLocationNormalized) => {
    const {user} = storeToRefs(useAuthStore());
    
    // Проверяем, есть ли пользователь и компания
    if (user.value && user.value.company) {
        const companyTypeId = user.value.company.company_type_id;
        
        // Если тип компании "Заказчик" (id=3), блокируем доступ
        if (companyTypeId === CUSTOMER_COMPANY_TYPE_ID) {
            // Перенаправляем на профиль
            return navigateTo('/profile?error=access_denied_portfolio');
        }
    }
});
