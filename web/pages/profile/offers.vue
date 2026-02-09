<template>
  <div>
    <div class="tabs" v-if="!route.params.id">
      <nuxt-link class="el-link text-bold" to="/profile/offers">Мои предложения</nuxt-link>
      <nuxt-link
          class="el-link text-bold"
          to="/profile/offers/employees"
          v-if="user.company_role==='boss'"
      >Предложения сотрудников
      </nuxt-link>
    </div>
    <NuxtPage/>
  </div>
</template>

<script setup lang="ts">

import {useAuthStore} from "~/stores/user";
import {useRoute, definePageMeta} from "#imports";

definePageMeta({
  middleware: 'block-customer-offers'
});

const {user} = useAuthStore();
const route = useRoute();
</script>

<style scoped lang="scss">
.tabs {
  display: flex;
  align-items: center;
  column-gap: 34px;
  margin-bottom: 20px;

  @media (min-width: 992px) {
    margin-bottom: 32px;
  }

  .el-link {
    padding: 8px 0;

    &.router-link-exact-active {
      border-bottom: 2px solid #EB8A00;
    }
  }
}
</style>