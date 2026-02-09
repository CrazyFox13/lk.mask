<template>
  <div class="page">
    <div class="container">
      <Preloader v-if="loading"/>
      <el-row v-else>
        <el-col :span="24">
          <div class="flex align-items-end favorite-title justify-between">
            <h1 class="text-h2 mb-0">Избранное</h1>
            <FavoriteSwitcher class="hidden-md-and-up" active-tab="orders"/>
          </div>
        </el-col>
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
              :hide-favorite="true"
              @show-filters="showFiltersDialog=true"
              :filters-exists="orderFiltersExists"
          />

          <Adv key="favorite_1" width="262" height="400" :banner="findBanner(banners, 'favorite_1')" class="mt-40"/>
          <Adv key="favorite_2" width="262" height="200" :banner="findBanner(banners, 'favorite_2')" class="mt-20"/>
          <Adv key="favorite_3" width="262" height="200" :banner="findBanner(banners, 'favorite_3')" class="mt-20"/>
        </el-col>
        <el-col :span="24" :md="18" class="col-pl-20">
          <div class="flex justify-between btn-bar">
            <FilterDialog
                class="hidden-md-and-up"
                v-model="filterQuery"
                @search="onSearch"
                :hide-favorite="true"
                @show-filters="showFiltersDialog=true"
                :filters-exists="orderFiltersExists"
            />

            <DropdownSelector
                v-if="totalCount>0"
                v-model="sortBy"
                :options="[
                    {key:'created_at_desc',text:'Сначала новые'},
                    {key:'created_at_asc',text:'Сначала старые'},
                ]"
                title="Сортировка"
            />

            <FavoriteSwitcher class="hidden-sm-and-down" active-tab="orders"/>
          </div>
          <div>
            <Preloader v-if="ordersFetching"/>
            <div v-else-if="totalCount>0">
              <OrderCard
                  class="order"
                  v-for="order in orders"
                  :key="order.id"
                  :order="order"
              />
              <div class="flex">
                <el-button @click="page++" class="pagination-btn" type="info" v-if="pagesCount>page">
                  Показать еще
                </el-button>
              </div>
            </div>

            <ContentNotFound v-else/>

            <YaAdv class="mt-20" ya-id="yandex_rtb_R-A-6812315-5" block-id="R-A-6812315-5"/>
          </div>
        </el-col>
      </el-row>
    </div>

    <OrderFiltersDialog
        v-if="showFiltersDialog"
        @close="showFiltersDialog=false"
    />
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import OrderCard from "~/components/Common/OrderCard.vue";
import {nextTick, useAsyncData, watch,findBanner,apiFetch} from "#imports";
import {objToQuery} from "~/composables/objToQuery";
import FilterDialog from "~/components/Orders/FilterDialog.vue";
import moment from "moment";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import FilterForm from "~/components/Orders/FilterForm.vue";
import {useDeviceStore} from "~/stores/device";
import {storeToRefs} from "pinia";
import {useNuxtApp} from "nuxt/app";
import {useOrderStore} from "~/stores/order";
import {type IOrderFilter} from "~/stores/order";
import DropdownSelector from "~/components/Common/DropdownSelector.vue";
import FavoriteSwitcher from "~/components/Favorite/FavoriteSwitcher.vue";
import Preloader from "~/components/Preloader.vue";
import Adv from "~/components/Adv.vue";
import OrderFiltersDialog from "~/components/Orders/OrderFiltersDialog.vue";
import YaAdv from "~/components/Adv/YaAdv.vue";

definePageMeta({
  middleware: ["auth"]
});

const orders = ref([]);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);

const {defaultFilters} = storeToRefs(useOrderStore());
const filterQuery = ref<IOrderFilter>(defaultFilters.value)
const showFiltersDialog = ref(false);
const orderFiltersExists = ref(false);
const filterForm = ref<any>(null);
const {isMobile} = storeToRefs(useDeviceStore())

const loading = ref(true);
const ordersFetching = ref(false);
const sortBy = ref<'created_at_asc' | 'created_at_desc'>('created_at_desc');

const nuxtApp = useNuxtApp();
nuxtApp.hook("page:finish", () => {
  loading.value = false;
});

const buildQuery = () => {
  const query = {
    sort_by: sortBy.value,
    take: 10,
    page: page.value,
    status: 'approved',
    ...filterQuery.value?.cities_id?.length ? {cities_id: filterQuery.value.cities_id} : {},
    ...filterQuery.value?.shifts?.length ? {shifts: filterQuery.value.shifts} : {},
    ...filterQuery.value?.date ? {date: moment(filterQuery.value.date).format('YYYY-MM-DD')} : {},
    ...filterQuery.value?.amount_by_agreement ? {amount_by_agreement: 1} : {},
    ...filterQuery.value?.amount_with_vat ? {amount_with_vat: 1} : {},
    ...filterQuery.value?.amount_cash ? {amount_cash: 1} : {},
    ...filterQuery.value?.with_company ? {with_company: 1} : {},
  };
  return objToQuery(query);
}

const getOrders = async () => {
  ordersFetching.value = true;
  const data = await apiFetch(`favorite/orders?${buildQuery()}`, {}, true);

  orders.value = page.value === 1 ? data.orders : [...orders.value, ...data.orders];
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  orderFiltersExists.value = data.orderFiltersExists;
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


const {banners} = await apiFetch(`adv?places=favorite_1,favorite_2,favorite_3`);
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

.mode-switcher {
  display: flex;
  align-items: center;
  column-gap: 20px;
}

.favorite-title {
  margin-bottom: 30px;
  @media (min-width: 992px) {
    margin-bottom: 40px;
  }
}
</style>