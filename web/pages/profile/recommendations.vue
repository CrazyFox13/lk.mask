<template>
  <div>
    <div class="tabs" v-if="!route.params.id">
      <nuxt-link class="el-link text-semibold" to="/profile/recommendations">
        Мои рекомендации
      </nuxt-link>
      <nuxt-link class="el-link text-semibold" to="/profile/recommendations/to-me" v-if="user.company_role==='boss'">
        В мой адрес
        <TabBadge :value="badges.recommendations_badges_value"/>
      </nuxt-link>
    </div>
    <NuxtPage/>
  </div>
</template>

<script setup lang="ts">
import {useAuthStore} from "~/stores/user";
import {useRoute, definePageMeta} from "#imports";
import {useBadgeStore} from "~/stores/badges";
import TabBadge from "~/components/Common/TabBadge.vue";

definePageMeta({
  middleware: 'block-customer-recommendations'
});

const {user} = useAuthStore();
const route = useRoute();
const {badges} = useBadgeStore();
</script>

<style scoped lang="scss">
.tabs {
  display: flex;
  align-items: center;
  column-gap: 13px;
  margin-bottom: 20px;

  @media (min-width: 992px) {
    column-gap: 34px;
    margin-bottom: 32px;
  }

  .el-link {
    padding: 8px 0;
    white-space: nowrap;
    font-size: 13px;
    line-height: 18px;
    @media (min-width: 992px) {
      font-size: 14px;
      line-height: 18px;
    }
    &.router-link-exact-active {
      border-bottom: 2px solid #EB8A00;
    }
  }
}
</style>