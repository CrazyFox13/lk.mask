<template>
  <div class="mob-menu">
    <nuxt-link @click="onClick(link)" :to="link.url" class="el-link text-gray mb-4" v-for="(link,k) in links" :key="k">
      <SvgIcon width="24px" height="24px" :name="link.icon"/>
      {{ link.title }}
    </nuxt-link>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {emitter} from "#imports";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {computed} from "#imports";

const {user} = storeToRefs(useAuthStore());

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

// URL для ссылки "Заявки" - для заказчиков ведет в профиль
const ordersUrl = computed(() => {
  return isCustomerCompany.value ? '/profile/orders' : '/orders';
});

const links = computed(() => [
  {url: ordersUrl.value, icon: "tractor", title: "Заявки"},
  {url: "/companies", icon: "users", title: "Исполнители"},
  {url: "/orders/create", icon: "m-menu-new-order", title: "Новая заявка"},
  {url: "/faq", icon: "comment", title: "Помощь"},
  {url: "/profile", icon: "user", title: "Профиль"},
]);

const onClick=(link:any)=>{
  emitter.emit('mobile-move', link);
}
// todo add badges and active color
</script>

<style lang="scss">
.mob-menu {
  position: fixed;
  bottom: 0;
  width: 100%;
  background: #FFFFFF;
  height: 72px;
  display: flex;
  justify-content: space-around;
  padding: 0;
  z-index: 3;
  border-top: 1px solid #E5E5E5;

  .el-link {
    font-size: 10px;
    line-height: 12px;
    font-weight: 500;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    row-gap: 5px;
  }
}
</style>