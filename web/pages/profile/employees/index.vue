<template>
  <div>
    <NoApprovedCompany v-if="noApprovedCompany()"/>
    <el-card v-else>
      <div class="flex align-items-center justify-between mb-10">
        <div class="text-h3">Список сотрудников</div>
        <nuxt-link v-if="isBoss" class="el-link el-button el-button--text text-black" to="/profile/employees/create">
          <span class="hidden-sm-and-down">Добавить сотрудника &nbsp;</span>
          <SvgIcon name="add"/>
        </nuxt-link>
      </div>
      <EmployeeListItem v-for="user in users" :user="user" :key="user.id"/>
    </el-card>
  </div>
</template>

<script setup lang="ts">

import NoApprovedCompany from "~/components/Profile/NoApprovedCompany.vue";
import {type IUser, useAuthStore} from "~/stores/user";
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, computed, ref} from "#imports";
import {storeToRefs} from "pinia";
import EmployeeListItem from "~/components/Profile/Employees/EmployeeListItem.vue";

const {noApprovedCompany} = useAuthStore();

const {user} = storeToRefs(useAuthStore());
const users = ref<IUser[]>([])

const isBoss = computed(() => user.value?.company_role === 'boss');

if (!noApprovedCompany()) {
  const data = await apiFetch(`companies/${user.value!.company_id}/employees`);
  users.value = data.users;
}
</script>

<style scoped lang="scss">

</style>