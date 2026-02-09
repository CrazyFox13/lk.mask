<template>
  <el-card class="no-confirmation" >
    <div class="flex align-items-center">
      <SvgIcon name="alert-red" class="mr-2"/>
      <div class="text-h5">Подтвердите ваши данные</div>
    </div>
    <p> Для того, чтобы создать компанию, необходимо подтвердить “Персональные данные"</p>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
</script>

<style scoped lang="scss">
.no-confirmation {
  .text-h5, p {
    margin: 0;
  }

  p {
    margin-top: 15px;
  }
}
</style>