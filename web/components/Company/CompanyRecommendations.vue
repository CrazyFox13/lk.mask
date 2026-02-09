<template>
  <div>
    <Preloader v-if="loading"/>
    <div v-else-if="totalCount>0">
      <CompanyRecommendationCard
          class="mb-20"
          v-for="recommendation in recommendations"
          :key="recommendation.id"
          :recommendation="recommendation"
      />
      <el-pagination
          class="mt-20"
          v-model:current-page="page"
          :page-size="TAKE"
          layout="prev, pager, next"
          :total="totalCount"
      />
    </div>

    <ContentNotFound v-else/>
  </div>
</template>

<script lang="ts" setup>
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import CompanyRecommendationCard from "~/components/Company/CompanyRecommendationCard.vue";
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch} from "#imports";
import Preloader from "~/components/Preloader.vue";

const TAKE = 10;
const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const recommendations = ref<any[]>([])

const route = useRoute();
const companyId = Number(route.params.id);


const getRecommendations = async () => {
  loading.value = true;
  const data = await apiFetch(`recommendations?company_id=${companyId}&filter=to_company&page=${page.value}&take=${TAKE}`, {}, true);
  recommendations.value = data.recommendations;
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getRecommendations());

watch(page, () => {
  getRecommendations();
});
</script>

<style scoped>

</style>