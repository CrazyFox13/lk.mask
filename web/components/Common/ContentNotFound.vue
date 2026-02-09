<template>
  <el-card class="flex justify-center">
    <div class="text-center">
      <el-image :src="Image" alt="Ничего не найдено"/>
      <div class="text-h4">Ничего не найдено</div>
    </div>
  </el-card>
</template>

<script setup>
import Image from '~/assets/images/content_no_found.png';
</script>

<style scoped lang="scss">
.text-h4 {
  margin-top: 10px;
}

.el-card {
  .el-card__body {
    text-align: center;
  }
}
</style>