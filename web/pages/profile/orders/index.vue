<template>
  <div>
    <ContentNotFound v-if="orders.length===0"/>
    <div v-else>
      <OrderCard
          v-for="order in orders"
          :order="order"
          :short="true"
          :employee="true"
          :key="order.id"
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
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch} from "#imports";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import OrderCard from "~/components/Common/OrderCard.vue";
import {useAuthStore} from "~/stores/user";

const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const orders = ref<any[]>([]);
const TAKE = 10;
const {user} = useAuthStore();
const getOrders = async () => {
  loading.value = true;
  const data = await apiFetch(`orders?company_id=${user!.company_id ? user!.company_id : ''}&user_id=${user!.id}&page=${page.value}&take=${TAKE}`, {}, true);
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

</script>

<style scoped lang="scss">

</style>