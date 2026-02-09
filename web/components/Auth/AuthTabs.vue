<template>
  <div class="flex">
    <NuxtLink class="el-link" to="/auth/sign-in" v-bind:class="{'active':tab==='sign-in'}">Вход</NuxtLink>
    <NuxtLink class="el-link" to="/auth/sign-up" v-bind:class="{'active':tab==='sign-up'}">Регистрация</NuxtLink>

  </div>
</template>

<script setup lang="ts">
const props = defineProps(['tab']);
</script>

<style scoped lang="scss">
.flex {
  .el-link {
    width: 50%;
    text-align: center;
    border-bottom: 2px solid #E8EBF1;
    padding-bottom: 8px;

    &.active {
      border-bottom: 2px solid #EB8A00;
    }
  }
}
</style>