<template>
  <div class="page">
    <div class="container">
      <Preloader v-if="loading"/>
      <el-row v-else>
        <el-col :span="24">
          <div class="flex align-items-end favorite-title justify-between">
            <h1 class="text-h2 mb-0">Избранное</h1>
            <FavoriteSwitcher class="hidden-md-and-up" active-tab="companies"/>
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

          <FilterForm ref="filterForm" v-model="filterQuery" v-on:update:modelValue="onSearch"/>

          <Adv key="favorite_1" width="262" height="400" :banner="findBanner(banners, 'favorite_1')" class="mt-40"/>
          <Adv key="favorite_2" width="262" height="200" :banner="findBanner(banners, 'favorite_2')" class="mt-20"/>
          <Adv key="favorite_3" width="262" height="200" :banner="findBanner(banners, 'favorite_3')" class="mt-20"/>
        </el-col>

        <el-col :span="24" :md="18" class="col-pl-20">
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
            <FavoriteSwitcher class="hidden-sm-and-down" active-tab="companies"/>
          </div>

          <Preloader v-if="companiesFetching"/>
          <div v-else-if="totalCount>0">
            <CompanyCard
                class="company"
                v-for="user in users"
                :key="user.id"
                :company="user.company"
                :employee="user"
            />
            <div class="flex">
              <el-button @click="page++" class="pagination-btn" type="info" v-if="pagesCount>page">
                Показать еще
              </el-button>
            </div>
          </div>

          <ContentNotFound v-else/>

          <YaAdv class="mt-20" ya-id="yandex_rtb_R-A-6812315-5" block-id="R-A-6812315-5"/>

        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import {useNuxtApp} from "nuxt/app";
import SvgIcon from "~/components/SvgIcon.vue";
import DropdownSelector from "~/components/Common/DropdownSelector.vue";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import CompanyCard from "~/components/Company/CompanyCard.vue";
import {nextTick, objToQuery, useAsyncData, watch, apiFetch, findBanner, ref} from "#imports";
import {type ICompanyFilter} from "~/stores/company";
import FilterForm from "~/components/Company/FilterForm.vue";
import FilterDialog from "~/components/Company/FilterDialog.vue";
import FavoriteSwitcher from "~/components/Favorite/FavoriteSwitcher.vue";
import Preloader from "~/components/Preloader.vue";
import Adv from "~/components/Adv.vue";
import YaAdv from "~/components/Adv/YaAdv.vue";

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
});

const users = ref<any[]>([]);

const buildQuery = () => {

  const query = {
    sort_by: sortBy.value,
    take: 10,
    page: page.value,
    ...filterQuery.value?.cities_id?.length ? {cities_id: filterQuery.value.cities_id} : {},
    ...filterQuery.value?.rating ? {rating: filterQuery.value.rating} : {},

  };
  return objToQuery(query);
}


const getCompanies = async () => {
  companiesFetching.value = true;
  const data = await apiFetch(`favorite/users?${buildQuery()}`, {}, true);

  users.value = page.value === 1 ? data.users : [...users.value, ...data.users];
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    companiesFetching.value = false;
  })
};

useAsyncData(() => getCompanies());

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

.favorite-title {
  margin-bottom: 30px;
  @media (min-width: 992px) {
    margin-bottom: 40px;
  }
}
</style>