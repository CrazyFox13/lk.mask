<template>
  <div>
    <div class="container">
      <div class="flex align-items-center header-content">
        <nuxt-link to="/" class="logo-wrapper">
          <img :src="Logo" alt="Логотип" class="logo"/>
        </nuxt-link>
        <SearchInput
            v-if="!isCustomerCompany && user"
            v-model="search"
            class="mb-0 search-input"
            placeholder="Введите название / номер компании"
            @submit="onSearch"
        />
        <div class="flex nav-menu">
          <nuxt-link :to="ordersUrl" class="el-link nav-link">
            Заявки
          </nuxt-link>
          <nuxt-link to="/companies" class="el-link nav-link">
            Исполнители
          </nuxt-link>
          <nuxt-link to="/faq" class="el-link nav-link" @click.prevent="goToFaq">
            Помощь
          </nuxt-link>
          <a 
            href="/orders/create"
            @click.prevent="goToCreateOrder"
            class="el-button el-button--primary is-plain prime-link"
          >
            <SvgIcon name="add-document" class="mr-2"/>
            Подать заявку
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

import SearchInput from "~/components/Common/Forms/SearchInput.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import Logo from "~/assets/images/logo-mask-group.png.jpg";
import {useRoute, useRouter} from "#imports";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {computed} from "#imports";

const router = useRouter();
const route = useRoute();
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

const search = ref(route.query.search ? route.query.search : '');
const onSearch = () => {
  router.push(`/companies?search=${search.value}`)
}

const goToCreateOrder = () => {
  console.log('goToCreateOrder called');
  router.push('/orders/create').catch((err) => {
    console.error('Router push error:', err);
    window.location.href = '/orders/create';
  });
}

const goToFaq = () => {
  router.push('/faq').catch((err) => {
    console.error('Router push error:', err);
    window.location.href = '/faq';
  });
}
</script>

<style scoped lang="scss">
.container {
  padding-top: 15px;
}

.header-content {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 40px;
}

.logo-wrapper {
  display: flex;
  align-items: center;
  margin-right: 20px;
  flex-shrink: 0;
}

.logo {
  height: 44px;
  width: auto;
  display: block;
}

.search-input {
  flex: 1 1 auto;
  max-width: 750px;
  min-width: 450px;
}

.nav-menu {
  margin-left: auto;
  margin-right: 0;
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}

.nav-link {
  font-size: 16px;
  font-weight: 500;
  line-height: 18px;
  color: #333;
  text-decoration: none;
  white-space: nowrap;
  
  &:hover {
    color: #409eff;
    text-decoration: none;
  }
}

.prime-link {
  padding: 6px 17px;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  line-height: 18px;
  white-space: nowrap;
  
  &:hover {
    text-decoration: none;
  }
  
  &:active {
    opacity: 0.8;
  }
}

</style>
