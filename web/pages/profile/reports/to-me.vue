<template>
  <div>
    <ContentNotFound v-if="reports.length===0"/>
    <div v-else>
      <ReportListItem
          class="mb-20"
          v-for="report in reports"
          :report="report"
          :key="report.id"
          @removed="reports.splice(i,1)"
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
import ReportListItem from "~/components/Profile/Reports/ReportListItem.vue";
import {useAuthStore} from "~/stores/user";
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch} from "#imports";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";

const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const reports = ref<any[]>([]);
const TAKE = 10;
const {user} = useAuthStore();
const getReports = async () => {
  loading.value = true;
  const data = await apiFetch(`reports?filter=to_company&company_id=${user!.company_id}&page=${page.value}&take=${TAKE}`, {}, true);
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

<style scoped lang="scss">

</style>