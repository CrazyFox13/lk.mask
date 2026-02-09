<template>
  <div>
    <ContentNotFound v-if="recommendations.length===0"/>
    <div v-else>
      <RecommendationListItem
          class="mb-20"
          v-for="(recommendation,i) in recommendations"
          :recommendation="recommendation"
          :key="recommendation.id"
          @removed="recommendations.splice(i,1)"
      />

      <el-pagination
          v-model:current-page="page"
          :page-size="TAKE"
          layout="prev, pager, next"
          :total="totalCount"
      />
    </div>
  </div>
</template>

<script setup lang="ts">

import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import RecommendationListItem from "~/components/Profile/Recommendations/RecommendationListItem.vue";
import {useAuthStore} from "~/stores/user";
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch, definePageMeta} from "#imports";

definePageMeta({
  middleware: 'block-customer-recommendations'
});

const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const recommendations = ref<any[]>([]);
const TAKE = 10;
const {user} = useAuthStore();

const getRecommendations = async () => {
  loading.value = true;
  const data = await apiFetch(`recommendations?filter=by_me&page=${page.value}&take=${TAKE}`, {}, true);
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

<style scoped lang="scss">

</style>