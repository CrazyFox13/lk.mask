<template>
  <div class="flex awards">
    <el-image class="award" :src="award.icon" v-for="award in company.awards" :key="award.id"/>
  </div>
</template>

<script setup lang="ts">

const props = defineProps(['company']);
</script>

<style scoped lang="scss">
.awards {
  column-gap: 8px;
  margin-top: 20px;
  margin-bottom: 20px;

  .award {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }

  @media (min-width: 992px) {
    margin-top: 0;
    margin-bottom: 0;
  }
}
</style>