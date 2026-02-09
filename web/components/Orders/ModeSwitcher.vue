<template>
  <div class="mode-switcher">
    <nuxt-link to="/orders" class="el-link el-link--primary hidden-sm-and-down">
      <SvgIcon name="list" class="mr-2"/>
      Список
    </nuxt-link>
    <nuxt-link to="/orders/map" class="el-link">
      <SvgIcon name="map" class="mr-2"/>
      Карта
    </nuxt-link>
  </div>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";
import {useRoute, useRouter, watch} from "#imports";

export type OrderViewMode = "list" | "map";
</script>

<style scoped>

.mode-switcher {
  display: flex;
  align-items: center;
  column-gap: 20px;
}
</style>