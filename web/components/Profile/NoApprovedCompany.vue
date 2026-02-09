<template>
  <el-card>
    <div class="flex align-items-center">
      <SvgIcon name="alert-red" class="mr-2"/>
      <h5>
        У вас нет подтвержденной компании
      </h5>
    </div>
    <p>Заполните информацию о своей компании, чтобы получить дополнительные возможности на портале</p>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";

</script>

<style scoped lang="scss">
h5, p {
  margin: 0;
}

p {
  margin-top: 15px;
}
</style>