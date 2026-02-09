<template>
  <div class="tabs">
    <nuxt-link
        class="el-link text-h6"
        to="/favorites/orders"
        v-bind:class="{'active':activeTab==='orders'}"
    >
      Заявки
    </nuxt-link>

    <nuxt-link
        class="el-link text-h6"
        to="/favorites/companies"
        v-bind:class="{'active':activeTab==='companies'}">
      Контрагенты
    </nuxt-link>
  </div>
</template>

<script lang="ts" setup>
const props = defineProps(['activeTab']);
</script>

<style scoped lang="scss">
.tabs {
  display: flex;
  align-items: center;
  column-gap: 20px;
}

.el-link.active {
  border-bottom: 2px solid #EB8A00;
}
</style>