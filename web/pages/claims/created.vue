<template>
  <div class="page">
    <div class="container">
      <el-card>
        <el-image :src="Image" alt="order created"/>
        <div class="text-h3 mt-20">Жалоба отправлена</div>
        <p class="text-gray">Мы получили вашу жалобу, без внимания ее не оставим</p>
        <div class="flex mt-20">
          <el-button type="primary" @click="back()">Перейти к заявке</el-button>
          <el-button type="primary" plain>Мои заявки</el-button>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import Image from "~/assets/images/claim_created.svg";
import {useRouter} from "#app";

const router = useRouter();
const back = () => {
  router.back();
}
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