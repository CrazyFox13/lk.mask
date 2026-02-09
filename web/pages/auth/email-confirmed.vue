<template>
  <div class="page">
    <div class="container">
      <el-card>
        <el-image :src="Image" alt="email confirmed"/>
        <div class="text-h2 mt-20 mb-0">Ваша почта подтверждена</div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import Image from "~/assets/images/email_confirmed.svg"
</script>

<style scoped lang="scss">
.el-card {
  text-align: center;

  .flex {
    flex-direction: column;
    row-gap: 10px;
    max-width: 425px;
    margin: auto;

    .el-button {
      margin-left: 0;
    }

    @media (min-width: 992px) {
      flex-direction: row;
      column-gap: 10px;
      & > * {
        flex: 1;
      }
    }
  }

  @media (min-width: 992px) {
    padding: 60px;
  }
}
</style>