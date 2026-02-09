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

          <FilterForm ref="filterForm" v-model="filterQuery" v-on:update:modelValue="onSearch"/>

          <Adv key="companies_list_1" width="262" height="400" :banner="findBanner(banners, 'companies_list_1')"
               class="mt-40"/>
          <Adv key="companies_list_2" width="262" height="200" :banner="findBanner(banners, 'companies_list_2')"
               class="mt-20"/>
          <Adv key="companies_list_3" width="262" height="200" :banner="findBanner(banners, 'companies_list_3')"
               class="mt-20"/>
          <YaAdv ya-id="yandex_rtb_R-A-6812315-3" block-id="R-A-6812315-3" :width="262" :height="400" class="mt-20"/>
          <YaAdv ya-id="yandex_rtb_R-A-6812315-4" block-id="R-A-6812315-4" :width="262" :height="400" class="mt-20"/>
        </el-col>

        <el-col :span="24" :md="18" class="col-pl-20">
          <el-row>
            <el-col :span="24" :md="18" class="col-pr-20 m-0">
              <h1 class="text-h2">Поставщики спецтехники</h1>
              <p class="text-sub-subtitle text-gray">
                Проверенные поставщики строительной техники.
                Удобный поиск по видам спецтехники и её базировании.
                Честный рейтинг, основанный на надежности компании.
              </p>
              <VehicleTypeDialog v-model="vehicleTypesId"/>
            </el-col>
            <el-col class="hidden-sm-and-down col-pl-20" :md="6">
              <el-image alt="smart companies" :src="SmartCompaniesImg"/>
            </el-col>
          </el-row>

          <div class="flex justify-between btn-bar">
            <!-- filter dialog -->
            <FilterDialog
                class="hidden-md-and-up"
                v-model="filterQuery"
                @search="onSearch"
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
          </div>

          <Preloader v-if="companiesFetching"/>
          <div v-else-if="totalCount>0">
            <CompanyCard
                class="company"
                v-for="company in companies"
                :key="company.id"
                :company="company"
            />
            <div class="flex">
              <el-button @click="page++" class="pagination-btn" type="info" v-if="pagesCount>page">
                Показать еще
              </el-button>
            </div>
          </div>

          <ContentNotFound v-else/>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import {useNuxtApp} from "nuxt/app";
import SvgIcon from "~/components/SvgIcon.vue";
import VehicleTypeDialog from "~/components/Common/Forms/VehicleTypeDialog.vue";
import SmartCompaniesImg from "~/assets/images/smart-companies.png";
import DropdownSelector from "~/components/Common/DropdownSelector.vue";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import CompanyCard from "~/components/Company/CompanyCard.vue";
import {nextTick, objToQuery, ref, useAsyncData, useRoute, watch, apiFetch, findBanner, useHead} from "#imports";
import {type ICompanyFilter} from "~/stores/company";
import FilterForm from "~/components/Company/FilterForm.vue";
import FilterDialog from "~/components/Company/FilterDialog.vue";
import Preloader from "~/components/Preloader.vue";
import Adv from "~/components/Adv.vue";
import YaAdv from "~/components/Adv/YaAdv.vue";

useHead(() => ({
  title: "Поставщики спецтехники в России - дилеры спецтехники, крупнейшие поставщики строительной, дорожной и грузовой техники",
  meta: [
    {
      name: 'description',
      content: "Профили ведущих поставщиков спецтехники и строительного оборудования. Крупнейшие дилеры и поставщики грузовой, дорожной, и складской техники в России. Настоящий рейтинг компаний с собственным парком техники.",
    },
  ],
}))

const loading = ref(true);
const nuxtApp = useNuxtApp();
nuxtApp.hook("page:finish", () => {
  loading.value = false;
});


const totalCount = ref(0);
const sortBy = ref<'created_at_asc' | 'created_at_desc'>('created_at_desc');
const companiesFetching = ref(false)
const pagesCount = ref(1);
const page = ref(1);
const filterQuery = ref<ICompanyFilter>({
  cities_id: [],
  rating: null,
})
const vehicleTypesId = ref<number[]>([])
const companies = ref<any[]>([]);

const route = useRoute();
const search = ref(route.query.search ? route.query.search : '');
watch(route, () => {
  search.value = route.query.search ? route.query.search : '';
}, {deep: true});

watch(search, () => {
  getCompanies();
})

const buildQuery = () => {
  const typesQuery = Object.values(vehicleTypesId.value).join(',');

  const query = {
    sort_by: sortBy.value,
    take: 10,
    page: page.value,
    search: search.value,
    workers: 1,
    ...typesQuery.length ? {vehicle_types_id: typesQuery} : {},
    ...filterQuery.value?.cities_id?.length ? {cities_id: filterQuery.value.cities_id} : {},
    ...filterQuery.value?.rating ? {rating: filterQuery.value.rating} : {},

  };
  return objToQuery(query);
}


const getCompanies = async () => {
  companiesFetching.value = true;
  const data = await apiFetch(`companies?${buildQuery()}`, {}, true);

  companies.value = page.value === 1 ? data.companies : [...companies.value, ...data.companies];
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    companiesFetching.value = false;
  })
};

useAsyncData(() => getCompanies());

watch(vehicleTypesId, () => {
  page.value = 1;
  getCompanies();
  getBanners();
});

watch(page, () => {
  getCompanies();
});

watch(sortBy, () => {
  getCompanies();
});

const onSearch = () => {
  page.value = 1;
  getCompanies()
}

const resetFilter = () => {
  filterQuery.value = {
    cities_id: [],
    rating: null
  };
}

const banners = ref<any[]>([]);
const getBanners = async () => {
  const data = await apiFetch(`adv?places=companies_list_1,companies_list_2,companies_list_3&vehicle_types_id=${vehicleTypesId.value}`);
  banners.value = data.banners;
}
useAsyncData(() => getBanners())

</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
}

.btn-bar {
  margin-top: 20px;
  margin-bottom: 20px;
}

.company {
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