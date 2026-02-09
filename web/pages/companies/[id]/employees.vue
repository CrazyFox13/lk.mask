<template>
  <div>
    <div>
      <Preloader v-if="loading"/>
      <el-card>
        <div class="list-item head hidden-sm-and-down">
          <div class="text-h5">Имя Фамилия</div>
          <div class="text-h5">Телефон</div>
        </div>
        <div class="list-item" v-for="user in users" :key="user.id">
          <p>{{ user.name }} {{ user.surname }}</p>
          <p class="text-blue">{{ formatPhone(user.phone) }}</p>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch, formatPhone} from "#imports";
import Preloader from "~/components/Preloader.vue";

const route = useRoute();
const companyId = Number(route.params.id);
const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const users = ref<any[]>([]);
const TAKE = 100;

const getUsers = async () => {
  loading.value = true;
  const data = await apiFetch(`companies/${companyId}/employees?page=${page.value}&take=${TAKE}`, {}, true);
  users.value = data.users;
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getUsers());

watch(page, () => {
  getUsers();
})
</script>

<style scoped lang="scss">
.list-item {
  display: flex;
  flex-flow: column;

  padding: 10px 0;
  border-bottom: 1px solid #E8EBF1;

  p, .text-h5 {
    margin: 0;
  }

  p:first-of-type {
    margin-bottom: 8px;
  }

  &.head {
    padding-bottom: 20px;
  }

  &:last-of-type {
    border-bottom: none;
  }

  @media (min-width: 992px) {
    justify-content: space-between;
    align-items: center;
    flex-flow: row;
    padding: 20px 0;
    p {
      margin: 0 !important;
    }
  }

}
</style>