<template>
  <section v-if="!isSupplierCompany" class="orders-section section">
    <div class="container">
      <div class="flex justify-between align-items-center title">
        <h2 class="text-h2 text-black">У нас можно арендовать</h2>
        <div class="hidden-md-and-up arrows">
          <el-button circle class="arrow" @click="onBack">
            <SvgIcon name="chevron-left"/>
          </el-button>
          <el-button circle class="arrow" @click="onForward">
            <SvgIcon name="chevron-right"/>
          </el-button>
        </div>
      </div>
      <Splide
          class="hidden-md-and-up"
          ref="splide"
          :options="splideOptions"
          @splide:moved="onChanged"
      >
        <SplideSlide v-for="group in vehicleGroups" :key="group.id">
          <el-card>
            <VehicleGroup :group="group"/>
          </el-card>
        </SplideSlide>
      </Splide>
      <DesktopList class="hidden-sm-and-down" :groups="vehicleGroups"/>
      <MobileApp/>
    </div>
  </section>
</template>

<script setup lang="ts">
import {Splide, SplideSlide} from '@splidejs/vue-splide';
import '@splidejs/vue-splide/css';
import {useFetch} from "#app";
import SvgIcon from "~/components/SvgIcon.vue";
import {watch, ref, onMounted, apiFetch, computed} from "#imports";
import VehicleGroup from "~/components/Index/OrdersSection/VehicleGroup.vue";
import DesktopList from "~/components/Index/OrdersSection/DesktopList.vue";
import MobileApp from "~/components/Index/OrdersSection/MobileApp.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const {user} = storeToRefs(useAuthStore());
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

// Проверяем, является ли компания пользователя типом "Поставщик" (не "Заказчик")
const isSupplierCompany = computed(() => {
  return user.value?.company?.company_type_id && user.value.company.company_type_id !== CUSTOMER_COMPANY_TYPE_ID;
});

const {vehicleGroups} = await apiFetch(`vehicle-groups?with_orders=1`, {}, true);
const item = ref(0)

const splide = ref();
const splideOptions = {
  perPage: 1, pagination: false, arrows: false, padding: {left: 0, right: 60}, gap: 0
};
const onBack = () => {
  if (item.value <= 0) return;
  // back
  item.value--;
  splide.value.splide.go(item.value)
}

const onForward = () => {
  if (item.value >= vehicleGroups?.length) return;
  // forward
  item.value++;
  splide.value.splide.go(item.value)
}

const onChanged = (e: any, index: number, prevIndex: number) => {
  item.value = index;
}
</script>

<style lang="scss">
.orders-section {
  /*padding-bottom: 32px;
  padding-top: 32px;*/

  /*@media (min-width: 992px) {
    padding-bottom: 80px;
    padding-top: 80px;
  }*/

  h2 {
    margin: 0;
  }

  .title {
    margin-bottom: 20px;

    @media (min-width: 992px) {
      margin-bottom: 40px;
    }
  }

  .arrows {
    .arrow {
      width: 30px;
      height: 30px;
      text-align: center;
      border-radius: 100px;
      box-shadow: 3px 3px 20px rgb(23 26 27 / 10%);
      border: none;
    }
  }

  .splide__slide {
    //padding-bottom: 20px;
    //padding: 15px;
    padding: 15px 0px 25px 0px;
    margin-right: 10px;
    margin-left: 15px;
    max-width: 250px;

    &.is-active {
      //margin-left: 0;
    }

    &.is-prev {
      //margin-right: 0;
    }

    .el-card {
      box-shadow: 6px 6px 24px 2px rgb(23 26 27 / 11%);
    }
  }
}
</style>