<template>
  <div class="flex text-sub-subtitle prices-list">
    <div class="price" v-if="amount_account_vat">
      <SvgIcon name="vat"/>
      {{ formatPrice(amount_account_vat) }}
    </div>
    <div class="price" v-if="amount_account">
      <SvgIcon name="no_vat"/>
      {{ formatPrice(amount_account) }}
    </div>
    <div class="price" v-if="amount_cash">
      <SvgIcon name="payment"/>
      {{ formatPrice(amount_cash) }}
    </div>
    <div class="price" v-if="amount_by_agreement">
      <SvgIcon name="payment"/>
      По договорённости
    </div>
  </div>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";
import {formatPrice} from "#imports";

const props = defineProps(['amount_account_vat','amount_account','amount_cash','amount_by_agreement']);
</script>

<style scoped lang="scss">
.prices-list {
  flex-wrap: wrap;
  row-gap: 8px;

  .price {
    font-weight: 400;
    font-size: 12px;
    line-height: 16px;
    display: flex;
    align-items: center;
    margin-right: 20px;

    &:last-of-type {
      margin-right: 0;
    }

    span {
      margin-right: 3px;
      display: flex;
      align-items: center;
    }
  }
}
</style>