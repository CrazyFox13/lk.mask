<template>
  <div class="page">
    <div class="container">
      <el-card>
        <el-image :src="Image" alt="order created"/>
        <div class="text-h3 mt-20">Ваше предложение успешно отредактировано</div>
        <div class="flex mt-20">
          <nuxt-link class="el-button el-button--primary is-plain" type="primary" plain :to="`/orders/${orderID}`">
            Вернуться к заявке
          </nuxt-link>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import Image from "~/assets/images/order_created.svg"
import {definePageMeta} from "#imports";

definePageMeta({
  middleware: 'block-customer-orders'
});

const route = useRoute();
const orderID= route.params.id;
</script>

<style scoped lang="scss">
.el-card {
  text-align: center;

  .flex {
    flex-direction: column;
    row-gap: 10px;
    max-width: 225px;
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