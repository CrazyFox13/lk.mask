<template>
  <div class="page">
    <div class="container">
      <el-card>
        <el-image :src="Image" alt="order created"/>
        <div class="text-h3 mt-20">Претензия отправлена</div>
        <p class="text-gray">Ответчик получил вашу претензию, ожидайте его ответа или пригласите Арбитра</p>
        <div class="flex mt-20">
          <nuxt-link class="el-button el-button--primary" to="/profile/reports" >Мои претензии</nuxt-link>
          <el-button type="primary" @click="back()" plain>Вернуться к контрагенту</el-button>
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