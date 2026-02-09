<template>
  <div>
    <EmployeeProfileCard :user="user"/>

    <div class="title">Заявки сотрудника</div>

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
import EmployeeProfileCard from "~/components/Profile/Employees/EmployeeProfileCard.vue";
import {apiFetch, nextTick, useAsyncData, useRoute, watch} from "#imports";
import {useAuthStore} from "~/stores/user";
import OrderCard from "~/components/Common/OrderCard.vue";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";

const store = useAuthStore();
const route = useRoute();

const {user} = await apiFetch(`companies/${store.user!.company_id}/employees/${route.params.id}`);

const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const orders = ref<any[]>([]);
const TAKE = 10;

const getOrders = async () => {
  loading.value = true;
  const data = await apiFetch(`orders?company_id=${store.user!.company_id}&user_id=${route.params.id}&page=${page.value}&take=${TAKE}`, {}, true);
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
})
</script>

<style scoped lang="scss">
.title {
  margin-top: 40px;
  margin-bottom: 20px;
  @media (min-width: 992px) {
    margin-top: 64px;
  }
}
</style>