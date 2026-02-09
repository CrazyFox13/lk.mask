<template>
  <div class="page">
    <div class="container">
      <el-card>
        <el-image :src="Image" alt="order created"/>
        <div class="text-h3 mt-20">Управление рассылками</div>
        <p class="text-gray">Вы действительно хотите отписаться от рассылки?</p>
        <div class="flex mt-20">
          <el-button type="primary" @click="confirm()">Отписаться</el-button>
          <el-button type="primary" plain @click="reject()">Не хочу</el-button>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import Image from "~/assets/images/claim_created.svg"
import {apiFetch, useRoute, useRouter} from "#imports";

const route = useRoute();
const router = useRouter();
const confirm = () => {
  apiFetch(`unsubscribe`, {
    method: "POST",
    body: route.query
  }).then(() => {
    // success
    router.push('/');
  }).catch(() => {
    // error
    alert("Ошибка! Не удалось отписаться. Свяжитесь с тех. поддержикой.")
  });
}

const reject = () => {
  router.push('/')
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