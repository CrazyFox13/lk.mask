<template>
  <section v-if="isLoggedIn && orders.length && !isSupplierCompany" class="latest-orders section bg-gray" :class="{'compact': isCompact}">
    <div class="container">
      <h2 class="text-h2 text-black">Последние заявки</h2>
      <el-row>
        <el-col :span="24" :md="18" :class="{'compact-orders': isCompact}">
          <OrderCard
              class="order"
              v-for="order in orders"
              :key="order.id"
              :order="order"
              :showStatusBadge="true"
              :to="`/profile/orders/${order.id}`"
          />

          <div class="text-center">
            <nuxt-link :to="allOrdersUrl" class="el-button el-button--primary to-orders">
              Все заявки
            </nuxt-link>
          </div>
        </el-col>
        <el-col :span="6" class="hidden-sm-and-down adv-col">
          <Adv key="main_1" width="262" height="400" :banner="findBanner(banners, 'main_1')" class="mb-20"/>
          <Adv key="main_2" width="262" height="400" :banner="findBanner(banners, 'main_2')" class="mb-20"/>
          <YaAdv ya-id="yandex_rtb_R-A-6812315-1" block-id="R-A-6812315-1" :width="262" :height="400"/>
        </el-col>
      </el-row>
    </div>
  </section>
</template>

<script setup lang="ts">
import OrderCard from "~/components/Common/OrderCard.vue";
import {apiFetch, findBanner, computed, ref} from "#imports";
import Adv from "~/components/Adv.vue";
import YaAdv from "~/components/Adv/YaAdv.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const {user} = storeToRefs(useAuthStore());
const isLoggedIn = computed(() => {
  return !!user.value;
});
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

// Проверяем, является ли компания пользователя типом "Поставщик" (не "Заказчик")
const isSupplierCompany = computed(() => {
  return user.value?.company?.company_type_id && user.value.company.company_type_id !== CUSTOMER_COMPANY_TYPE_ID;
});

// Загружаем заявки в зависимости от типа компании
const orders = ref<any[]>([]);

const allowedStatuses = ['approved', 'on_approval', 'in_progress'];

// Загружаем заявки только если пользователь авторизован
if (user.value) {
  const currentUser = user.value;
  if (currentUser.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID) {
    // Для заказчиков загружаем их заявки из профиля
    const ordersData = await apiFetch(`orders?company_id=${currentUser.company_id ? currentUser.company_id : ''}&user_id=${currentUser.id}&sort_by=created_at_desc&take=20`, {}, true);
    orders.value = (ordersData.orders || []).filter((order: any) => allowedStatuses.includes(order.moderation_status)).slice(0, 7);
  } else {
    // Для остальных - общие последние заявки
    const ordersData = await apiFetch(`orders?sort_by=created_at_desc&take=20`, {}, true);
    orders.value = (ordersData.orders || []).filter((order: any) => allowedStatuses.includes(order.moderation_status)).slice(0, 7);
  }
}

// URL для кнопки "Все заявки" - для заказчиков ведет в профиль
const allOrdersUrl = computed(() => {
  return isCustomerCompany.value ? '/profile/orders' : '/orders';
});

const {banners} = await apiFetch(`adv?places=main_1,main_2`);

const isCompact = computed(() => {
  return orders.value.length > 0 && orders.value.length <= 2;
});


</script>

<style scoped lang="scss">
.latest-orders {

  h2 {
    margin-bottom: 32px;
  }

  .to-orders {
    width: 100%;
    padding: 11px 20px;
  }

  @media (min-width: 992px) {
    /*padding-top: 80px;
    padding-bottom: 80px;*/

    .to-orders {
      max-width: 150px;
      margin: auto;
    }

    .adv-col {
      padding-left: 40px;

      .el-image {
        margin-bottom: 20px;
      }
    }
  }

  .compact-orders {
    .order {
      margin-bottom: 10px;
    }
  }

  &.compact {
    padding-bottom: 20px;
  }
}
</style>