<template>
  <div class="page">
    <div class="container">
      <el-card>
        <el-image :src="Image" alt="order created"/>
        <div class="text-h3 mt-20">Сотрудник добавлен</div>
        <p class="text-gray">Вашему сотруднику необходимо подтвердить контактные данные</p>
        <div class="flex mt-20">
          <nuxt-link class="el-button el-button--primary" to="/profile/employees/create">Добавить нового</nuxt-link>
          <nuxt-link class="el-button el-button--primary is-plain"  to="/profile/employees">Мои сотрудники</nuxt-link>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import Image from "~/assets/images/employee_created.svg";
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