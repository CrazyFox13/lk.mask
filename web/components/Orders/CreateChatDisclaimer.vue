<template>
  <el-card>
    <div class="disclaimer">
      <div class="disclaimer-alert">!</div>
      <div class="disclaimer-text text-h5">Сообщения чата по заявке будут приходить только в приложении</div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
</script>

<style scoped lang="scss">
.disclaimer {
  display: flex;
  column-gap: 16px;

  &-alert {
    background: #2D6679;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ffffff;
    border-radius: 50%;
    font-weight: 800;
    font-size: 22px;
    line-height: 20px;
  }

  &-text {
    flex: 1;
  }
}
</style>