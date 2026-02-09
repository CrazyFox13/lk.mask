<template>
  <div class="page">
    <div class="container">
      <Preloader v-if="loading"/>
      <OrderMap
          v-else
          @onBounds="onBounds"
          :orders="orders"
          @onLoad="mapLoaded=true"
      />

      <el-card class="filter-card hidden-sm-and-down" v-if="mapLoaded">
        <div class="flex align-items-center justify-between mb-30">
          <div class="text-h4 flex align-items-center">
            <SvgIcon name="filter" class="mr-2"/>
            Фильтры
          </div>
          <el-button link class="text-sub-subtitle flex align-items-center text-gray m-0" @click="resetFilter()">
            Сбросить
            <SvgIcon name="close"/>
          </el-button>
        </div>
        <FilterForm
            ref="filterForm"
            v-model="filterQuery"
            v-on:update:modelValue="onSearch"
            @save-filter="saveFilterDialog=true"
            :filter-changed="availableToSave"
            @show-filters="showFiltersDialog=true"
            :filters-exists="orderFiltersExists"
        />
      </el-card>
    </div>

    <div class="flying-actions" v-if="mapLoaded">
      <FilterDialog
          class="hidden-md-and-up"
          v-model="filterQuery"
          @search="onSearch"
          @save-filter="saveFilterDialog=true"
          :filter-changed="availableToSave"
          @show-filters="showFiltersDialog=true"
          :filters-exists="orderFiltersExists"
      >
        <template #trigger="{action}">
          <el-button @click="action">
            <SvgIcon name="filter"/>
          </el-button>
        </template>
      </FilterDialog>

      <VehicleTypeDialog
          @vehicle-string="onVehicleStringChanged"
          v-model="vehicleTypesId"
          v-on:update:modelValue="onSearch"
          :white="true"
      />

      <nuxt-link class="el-button" href="/orders">
        <SvgIcon name="list" class="mr-2"/>
        Список
      </nuxt-link>
    </div>

    <SaveFilterDialog
        v-if="saveFilterDialog"
        @close="saveFilterDialog=false"
        :search-name="searchName"
        :filterQuery="filterSavingValue"
    />

    <OrderFiltersDialog
        v-if="showFiltersDialog"
        @close="showFiltersDialog=false"
    />
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import VehicleTypeDialog from "~/components/Common/Forms/VehicleTypeDialog.vue";
import {
  apiFetch,
  computed,
  nextTick,
  objToQuery,
  onBeforeRouteLeave,
  onMounted, ref,
  useAsyncData,
  useRoute,
  watch,
  definePageMeta
} from "#imports";

definePageMeta({
  middleware: 'block-customer-orders'
});
import FilterDialog from "~/components/Orders/FilterDialog.vue";
import moment from "moment";
import FilterForm from "~/components/Orders/FilterForm.vue";
import OrderMap from "~/components/Orders/OrderMap.vue";
import {storeToRefs} from "pinia";
import {useNuxtApp} from "nuxt/app";
import {useOrderStore} from "~/stores/order";
import {type IOrderFilter} from "~/stores/order";
import Preloader from "~/components/Preloader.vue";
import SaveFilterDialog from "~/components/Orders/SaveFilterDialog.vue";
import OrderFiltersDialog from "~/components/Orders/OrderFiltersDialog.vue";
import {type RouteLocationNormalizedLoaded} from "vue-router";

const route = useRoute();
const orders = ref([]);
const totalCount = ref(0);

const defaultVehicleTypesId = <string>route.query.vehicle_types_id;
const vehicleTypesId = ref<number[]>(defaultVehicleTypesId ? defaultVehicleTypesId.split(",").map(Number) : [])
const bounds = ref<{ center: number[], zoom: number }>({
  center: [],
  zoom: 0,
});
const {defaultFilters} = storeToRefs(useOrderStore());
const filterQuery = ref<IOrderFilter>(defaultFilters.value)
const filterForm = ref<any>(null);
const loading = ref(true);
const ordersFetching = ref(false);
const mapLoaded = ref(false);
const searchName = ref();
const nuxtApp = useNuxtApp();
nuxtApp.hook("page:finish", () => {
  setupFilterFromRoute(route)
  loading.value = false;
});

onMounted(()=>{
  const body = document.querySelector("body");
  body?.classList.add("overflow-hidden");

  const footer = document.querySelector(".footer");
  footer?.classList.add("hidden")
})

onBeforeRouteLeave(()=>{
  const body = document.querySelector("body");
  body?.classList.remove("overflow-hidden");

  const footer = document.querySelector(".footer");
  footer?.classList.remove("hidden")
})

watch(route, (to) => {
  setupFilterFromRoute(to);
}, {deep: true})

const setupFilterFromRoute = (to: RouteLocationNormalizedLoaded) => {
  vehicleTypesId.value = [];
  resetFilter();
  Object.keys(to.query).forEach((key: string) => {
    if (key in filterQuery.value) {
      if (key === 'cities_id') {
        filterQuery.value.cities_id = (to.query.cities_id as string).split(",").map(Number)
      } else {
        const isNumeric = !isNaN(Number(to.query[key]));
        (filterQuery.value as any)[key] = isNumeric ? Number(to.query[key]) : to.query[key];
      }
    }
  });
  if (to.query.vehicle_types_id) {
    vehicleTypesId.value = (to.query.vehicle_types_id as string).split(",").map(Number)
  }
}

const filterSettings = computed(() => {
  const citiesValue = filterQuery.value?.cities_id;
  const citiesExists = Array.isArray(citiesValue) && citiesValue.length > 0;
  return {
    ...citiesExists ? {cities_id: citiesValue} : {},
    ...filterQuery.value?.shifts?.length ? {shifts: filterQuery.value.shifts} : {},
    ...filterQuery.value?.date ? {date: moment(filterQuery.value.date).format('YYYY-MM-DD')} : {},
    ...filterQuery.value?.amount_by_agreement ? {amount_by_agreement: 1} : {},
    ...filterQuery.value?.amount_with_vat ? {amount_with_vat: 1} : {},
    ...filterQuery.value?.amount_cash ? {amount_cash: 1} : {},
    ...filterQuery.value?.with_company ? {with_company: 1} : {},
  }
})

const buildQuery = () => {
  const typesQuery = Object.values(vehicleTypesId.value).join(',');
  return {
    take: -1,
    status: 'approved',
    geo: geoData.value,
    ...typesQuery.length ? {vehicle_types_id: typesQuery} : {},
    ...filterSettings.value,
    ...filterQuery.value?.favorite ? {favorite: 1} : {},
  };
}

const filterSavingValue = computed(() => {
  return JSON.stringify(buildQuery());
})

const availableToSave = ref(false);
const getOrders = async () => {
  ordersFetching.value = true;
  const data = await apiFetch(`orders?${objToQuery(buildQuery())}`, {}, true);
  orders.value = data.orders;
  totalCount.value = data.totalCount;
  orderFiltersExists.value = data.orderFiltersExists;
  availableToSave.value = data.availableToSave;
  await nextTick(() => {
    ordersFetching.value = false;
  })
};

useAsyncData(() => getOrders())

const onSearch = () => {
  getOrders()
}

const onBounds = (v: { center: number[], zoom: number }) => {
  bounds.value = v;
}

watch(bounds, (newV, oldV) => {
  if (JSON.stringify(newV) !== JSON.stringify(oldV)) getOrders();
}, {deep: true});

const geoData = computed(() => {
  const radius = 10e9 / Math.exp(bounds.value.zoom) * .75;
  return [bounds.value.center[0], bounds.value.center[1], radius];
})

const resetFilter = () => {
  filterQuery.value = {
    cities_id: [],
    shifts: '',
    date: undefined,
    amount_by_agreement: false,
    amount_with_vat: false,
    amount_cash: false,
    with_company: false,
    favorite: false,
  };
}

const onVehicleStringChanged = (vehicleString: string) => {
  searchName.value = vehicleString;
}

const saveFilterDialog = ref(false);
const showFiltersDialog = ref(false);
const orderFiltersExists = ref(false);
</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
}

.filter-card {
  position: absolute;
  z-index: 44;
  background: white;
  padding: 20px;
  width: 262px;
  top: 5px;
  transform: scale(0.5);
  transform-origin: top left;
  @media (min-width: 1280px) {
    transform: scale(0.7);
  }
  @media (min-width: 1440px) {
    transform: scale(0.8);
  }
  @media (min-width: 1750px) {
    transform: scale(0.9);
  }

}

.flying-actions {
  position: absolute;
  z-index: 4444444;
  right: 10px;
  top: 5px;
  display: flex;
  -moz-column-gap: 20px;
  column-gap: 20px;
  max-width: calc(100% - 20px);

  .el-button {
    height: 44px;
    padding: 8px;
    border: none;
    box-shadow: 4px 4px 25px 0px rgba(36, 34, 34, 0.15);

    &:hover {
      background: #ffffff;
      border-color: inherit;
      border: none;
    }
  }
}
</style>