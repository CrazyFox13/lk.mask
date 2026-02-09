<template>
  <el-card>
    <div class="flex justify-between mb-20">
      <div class="flex align-items-center">
        <nuxt-link class="el-button el-button--info link-circle mr-5" to="/profile/employees">
          <SvgIcon name="back"/>
        </nuxt-link>
        <div class="text-h3 mr-15 hidden-sm-and-down">{{ user.name }} {{ user.surname }}</div>
        <ConfirmationLabel class="hidden-sm-and-down" :confirmed="!!user.email_verified_at"/>
      </div>
      <div v-if="isBoss">
        <nuxt-link class="el-button el-button--info link-circle mr-3" :to="`/profile/employees/${user.id}/edit`">
          <SvgIcon name="pencil"/>
        </nuxt-link>
        <nuxt-link :to="`/profile/employees/${user.id}/delete`" class="el-button el-button--info link-circle">
          <SvgIcon name="trash"/>
        </nuxt-link>
      </div>
    </div>
    <ConfirmationLabel :reverse="true" :permanent="true" class="text-sub-subtitle hidden-md-and-up mb-2"
                       :confirmed="!!user.email_verified_at"/>
    <div class="text-h3 mr-5 mb-3 hidden-md-and-up">{{ user.name }} {{ user.surname }}</div>
    <div class="flex bar">
      <div>{{ formatPhone(user.phone) }}</div>
      <div>{{ user.email }}</div>
      <div class="flex align-items-center">
        <SvgIcon name="orders" class="mr-2"/>
        {{ user.orders_count }} заявок
      </div>
      <div class="flex align-items-center">
        <SvgIcon name="report-confirmed" class="mr-2"/>
        {{ user.active_orders_count }} активных
      </div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import ConfirmationLabel from "~/components/Profile/Employees/ConfirmationLabel.vue";
import {formatPhone, computed} from "#imports";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

defineProps(['user']);

const {user: currentUser} = storeToRefs(useAuthStore());
const isBoss = computed(() => currentUser.value?.company_role === 'boss');
</script>

<style scoped lang="scss">
.bar {
  column-gap: 10px;
  flex-wrap: wrap;
  row-gap: 5px;

  @media (min-width: 992px) {
    column-gap: 20px;
  }
}
</style>