<template>
  <div>
    <Preloader v-if="loading"/>
    <div v-else-if="totalCount>0">
      <CompanyReportCard
          class="mb-20"
          v-for="report in reports"
          :key="report.id"
          :report="report"
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

<script setup lang="ts">
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import CompanyReportCard from "~/components/Company/CompanyReportCard.vue";
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch} from "#imports";
import Preloader from "~/components/Preloader.vue";

const TAKE = 10;
const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const reports = ref<any[]>([]);

const route = useRoute();
const companyId = Number(route.params.id);

const getReports = async () => {
  loading.value = true;
  const data = await apiFetch(`reports?company_id=${companyId}&filter=to_company&page=${page.value}&take=${TAKE}&statuses=confirmed%2Cresolved`, {}, true);
  reports.value = data.reports;
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getReports());

watch(page, () => {
  getReports();
});
</script>

<style scoped>

</style>