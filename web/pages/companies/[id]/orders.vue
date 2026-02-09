<template>
  <div>
    <Preloader v-if="loading"/>
    <div v-else>
      <div class="flex gap-6 mt-30 mb-20 tabs">
        <a role="button" class="el-link text-bold" v-bind:class="{'active':tab==='active'}"
           @click="changeTab('active')">Активные</a>
        <a role="button" class="el-link text-bold" v-bind:class="{'active':tab==='completed'}"
           @click="changeTab('completed')">Завершенные</a>
      </div>
      <template v-if="totalCount>0">
        <OrderCard
            class="order"
            v-for="order in orders"
            :key="order.id"
            :order="order"
            :short="true"
        />
        <el-pagination
            v-model:current-page="page"
            :page-size="TAKE"
            layout="prev, pager, next"
            :total="totalCount"
        />
      </template>
      <ContentNotFound v-else/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {nextTick, useAsyncData, apiFetch, watch} from "#imports";
import OrderCard from "~/components/Common/OrderCard.vue";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import Preloader from "~/components/Preloader.vue";

const route = useRoute();
const companyId = Number(route.params.id);
const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const orders = ref<any[]>([]);
const TAKE = 10;

const tab = ref<"active" | "completed">("active");

const getOrders = async () => {
  loading.value = true;
  const data = await apiFetch(`orders?is_active=${tab.value === 'active' ? 1 : 0}&is_finished=${tab.value === 'completed' ? 1 : 0}&company_id=${companyId}&page=${page.value}&take=${TAKE}`, {}, true);
  orders.value = data.orders;
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getOrders());

watch(page, () => {
  getOrders();
});

const changeTab = (t: "active" | "completed") => {
  tab.value = t;
  getOrders();
}
</script>

<style scoped lang="css">
.tabs {
  .active {
    border-bottom: 2px solid #EB8A00;
  }
}
</style>