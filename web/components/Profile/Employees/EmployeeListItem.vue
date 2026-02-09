<template>
  <nuxt-link :to="`/profile/employees/${user.id}`" class="employee">
    <div class="flex align-items-center justify-between mb-10">
      <div class="text-h5">{{ user.name }} {{ user.surname }}</div>
      <ConfirmationLabel :confirmed="!!user.email_verified_at"/>
    </div>
    <div class="flex bar">
      <div>{{ formatPhone(user.phone) }}</div>
      <div>{{ user.email }}</div>
      <div class="hidden-md-and-up" style="flex:100%"></div>
      <div class="flex align-items-center">
        <SvgIcon name="orders" class="mr-2"/>
        {{ user.orders_count }} заявок
      </div>
      <div class="flex align-items-center">
        <SvgIcon name="report-confirmed" class="mr-2"/>
        {{ user.active_orders_count }} активных
      </div>
    </div>
  </nuxt-link>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";
import {formatPhone} from "#imports";
import ConfirmationLabel from "~/components/Profile/Employees/ConfirmationLabel.vue";

defineProps(['user']);
</script>

<style scoped lang="scss">
.employee {
  border-top: 1px solid #E8EBF1;
  padding-top: 20px;
  padding-bottom: 20px;
  text-decoration: none;
  color: inherit;
  display: block;

  .bar {
    column-gap: 10px;
    flex-wrap: wrap;
    row-gap: 5px;

    @media (min-width: 992px) {
      column-gap: 20px;
    }
  }
}
</style>