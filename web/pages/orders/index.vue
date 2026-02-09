<template>
  <div class="page">
    <div class="container">
      <Preloader v-if="loading"/>
      <el-row v-else>
        <el-col class="hidden-sm-and-down col-pr-20" :span="6">
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

          <Adv key="orders_list_1" width="262" height="400" :banner="findBanner(banners, 'orders_list_1')"
               class="mt-40"/>
          <Adv key="orders_list_2" width="262" height="200" :banner="findBanner(banners, 'orders_list_2')"
               class="mt-20"/>
          <Adv key="orders_list_3" width="262" height="200" :banner="findBanner(banners, 'orders_list_3')"
               class="mt-20"/>
          <YaAdv ya-id="yandex_rtb_R-A-6812315-2" block-id="R-A-6812315-2" :width="262" :height="400" class="mt-20"/>
        </el-col>
        <el-col :span="24" :md="18" class="col-pl-20">
          <el-row>
            <el-col :span="24" :md="18" class="col-pr-20 m-0">
              <h1 class="text-h2">Заявки на спецтехнику</h1>
              <p class="text-sub-subtitle text-gray">
                В этом разделе располагаются все заявки от организаций и частных лиц.
                Вы можете откликнуться на заявку.
                Для вашего удобства на странице предусмотренны фильтры для быстрого поиска релевантных заявок
              </p>
              <VehicleTypeDialog
                  ref="vtdialog"
                  @vehicle-string="onVehicleStringChanged"
                  v-model="vehicleTypesId"
                  v-on:update:modelValue="onSearch"
              />
            </el-col>
            <el-col class="hidden-sm-and-down col-pl-20" :md="6">
              <el-image alt="smart tasks" :src="SmartTaskImg"/>
            </el-col>
          </el-row>

          <!-- Карточки по категориям -->
          <CategoryCards
              v-model="vehicleTypesId"
              @search="onVehicleCardSelected"
              @showModal="onTypeDialogShow"
          />

          <!-- Фильтры -->
          <div class="flex justify-between btn-bar">
            <FilterDialog
                class="hidden-md-and-up"
                v-model="filterQuery"
                @search="onSearch"
                @save-filter="saveFilterDialog=true"
                :filter-changed="availableToSave"
                @show-filters="showFiltersDialog=true"
                :filters-exists="orderFiltersExists"
            />

            <div style="display: flex;column-gap:16px;">
              <DropdownSelector
                  v-if="totalCount>0"
                  v-model="sortBy"
                  :options="[
                    {key:'created_at_desc',text:'Сначала новые'},
                    {key:'created_at_asc',text:'Сначала старые'},
                ]"
                  title="Сортировка"
              />
              <DropdownSelector
                  :model-value="onlyMyVehicles?'vehicle':'all'"
                  classList="hidden-sm-and-down"
                  :options="[
                    {key:'all',text:'Все'},
                    {key:'vehicle',text: company && company.vehicle_types_id && company.vehicle_types_id.length?'На мою технику':'Выбрать технику'},
                  ]"
                  title="Показывать"
                  @update:model-value="onTabChanged"
              />
            </div>

            <ModeSwitcher/>
          </div>

          <!-- Табы "Все", "На вашу технику", "Рядом с вами" -->
          <OrderTabs class="hidden-md-and-up" :state="onlyMyVehicles?'vehicle':'all'" @changed="onTabChanged"/>

          <Preloader v-if="ordersFetching"/>
          <div v-else-if="totalCount>0">
            <OrderCard
                class="order"
                v-for="order in orders"
                :key="order.id"
                :order="order"
            />
            <div class="flex mb-20">
              <el-button @click="page++" class="pagination-btn" type="info" v-if="pagesCount>page">
                Показать еще
              </el-button>
            </div>
            <el-pagination
                v-model:current-page="page"
                :page-size="10"
                layout="prev, pager, next"
                :total="totalCount"
            />
          </div>
          <ContentNotFound v-else/>
        </el-col>
      </el-row>
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
import OrderCard from "~/components/Common/OrderCard.vue";
import VehicleTypeDialog from "~/components/Common/Forms/VehicleTypeDialog.vue";
import {
  apiFetch,
  computed,
  findBanner,
  nextTick,
  objToQuery,
  useAsyncData,
  useRoute,
  watch,
  ref, useRouter, definePageMeta, useHead,
} from "#imports";
import FilterDialog from "~/components/Orders/FilterDialog.vue";
import moment from "moment";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import FilterForm from "~/components/Orders/FilterForm.vue";
import SmartTaskImg from "~/assets/images/smart-tasks.png";
import ModeSwitcher from "~/components/Orders/ModeSwitcher.vue";
import {storeToRefs} from "pinia";
import {useNuxtApp} from "nuxt/app";
import {useOrderStore} from "~/stores/order";
import {type IOrderFilter} from "~/stores/order";
import DropdownSelector from "~/components/Common/DropdownSelector.vue";
import Preloader from "~/components/Preloader.vue";
import Adv from "~/components/Adv.vue";
import SaveFilterDialog from "~/components/Orders/SaveFilterDialog.vue";
import OrderFiltersDialog from "~/components/Orders/OrderFiltersDialog.vue";
import {type RouteLocationNormalizedLoaded} from "vue-router";
import YaAdv from "~/components/Adv/YaAdv.vue";
import CategoryCards from "~/components/Orders/CategoryCards.vue";
import OrderTabs from "~/components/Orders/OrderTabs.vue";
import {type ICompany, useAuthStore} from "~/stores/user";

definePageMeta({
  middleware: 'block-customer-orders'
});

const route = useRoute();
const orders = ref([]);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const {user} = storeToRefs(useAuthStore());
const company = ref<ICompany | undefined>(user.value && user.value.company ? user.value.company : undefined)
const onlyMyVehicles = ref(false)

const defaultVehicleTypesId = <string>route.query.vehicle_types_id;
const vehicleTypesId = ref<number[]>(defaultVehicleTypesId ? defaultVehicleTypesId.split(",").map(Number) : [])
const {defaultFilters} = storeToRefs(useOrderStore());
const filterQuery = ref<IOrderFilter>(defaultFilters.value)
const filterForm = ref<any>(null);
const vtdialog = ref<any>(null);
const loading = ref(true);
const ordersFetching = ref(false);
const sortBy = ref<'created_at_asc' | 'created_at_desc'>('created_at_desc');
const searchName = ref();
const nuxtApp = useNuxtApp();

useHead(() => ({
  title: "Заявки на аренду спецтехники на бирже - найти заказы и работу на спецтехнику, поиск заказчиков на технику",
  meta: [
    {
      name: 'description',
      content: "Найдите заявку на свою спецтехнику на бирже ASTT.SU. Работа без посредников. Новые заявки каждый день. Безопасно. Помощь новым пользователям. Находите новых клиентов с нами!",
    },
  ],
}))

nuxtApp.hook("page:finish", () => {
  setupFilterFromRoute(route)
  loading.value = false;
});

watch(route, (to: any) => {
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
  return {
    sort_by: sortBy.value,
    take: 10,
    page: page.value,
    status: 'approved',
    for_company_vehicles: (onlyMyVehicles.value && company.value) ? company.value.id : '',
    ...vehicleTypesId.value.length ? {vehicle_types_id: vehicleTypesId.value} : {},
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

  orders.value = page.value === 1 ? data.orders : [...orders.value, ...data.orders];
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  orderFiltersExists.value = data.orderFiltersExists;
  availableToSave.value = data.availableToSave;
  await nextTick(() => {
    ordersFetching.value = false;
  })
};

useAsyncData(() => getOrders())

const onSearch = () => {
  page.value = 1;
  getOrders()
}

watch(page, () => {
  getOrders();
});

watch(sortBy, () => {
  getOrders();
});

const resetFilter = () => {
  onlyMyVehicles.value = false;
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

const banners = ref<any[]>([]);
const getBanners = async () => {
  const data = await apiFetch(`adv?places=orders_list_1,orders_list_2,orders_list_3&vehicle_types_id=${vehicleTypesId.value}`);
  banners.value = data.banners;
}
useAsyncData(() => getBanners());

const onVehicleStringChanged = (vehicleString: string) => {
  searchName.value = vehicleString;
}

const saveFilterDialog = ref(false);
const showFiltersDialog = ref(false);
const orderFiltersExists = ref(false);

const onTypeDialogShow = () => {
  if (vtdialog.value && typeof vtdialog.value.onInputActive === 'function') {
    vtdialog.value.onInputActive();
  } else {
    console.warn('onInputActive не найден или vtdialog еще не инициализирован');
  }
}

const router = useRouter();
const onTabChanged = (state: "all" | "vehicle" | "map") => {
  switch (state) {
    case "all":
      resetFilter()
      break;
    case "vehicle":
      if (company.value && company.value.vehicle_types_id && company.value.vehicle_types_id.length) {
        vehicleTypesId.value = []; // Сбрасываем карточки, включаем компанию
        onlyMyVehicles.value = true;
        onSearch();
      } else {
        router.push('/profile/company');
      }
      break;
    case "map":
      break;

  }
}

const onVehicleCardSelected = ()=>{
  onlyMyVehicles.value = false;
  onSearch()
}
</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
}

.btn-bar {
  margin-top: 20px;
  margin-bottom: 20px;
}

.order {
  margin-bottom: 20px;
}

.pagination-btn {
  width: 100%;
  @media (min-width: 992px) {
    max-width: 150px;
    margin: auto;
  }
}

@media (min-width: 992px) {
  .col-pr-20 {
    padding-right: 20px;
  }

  .col-pl-20 {
    padding-left: 20px;
  }
}


</style>