<template>
  <el-row>
    <el-col :span="24" :md="12">
      <h5 class="text-h5">Дата регистрации компании в ИФНС:</h5>
      <p class="text-large mt-0">
        {{ company.legal_registration_date ? moment(company.legal_registration_date).format("DD.MM.YYYY") : '-' }}
        <span v-if="company.legal_registration_date">({{
            moment().diff(moment(company.legal_registration_date), 'days')
          }} дней)</span>
      </p>
    </el-col>
    <el-col :span="24" :md="12">
      <h5 class="text-h5">Дата регистрации компании на портале ASTT.SU:</h5>
      <p class="text-large mt-0">
        {{ moment(company.created_at).format("DD.MM.YYYY") }}
        <span v-if="company.created_at">({{
            moment().diff(moment(company.created_at), 'days')
          }} дней)</span>
      </p>
    </el-col>
    <el-col :span="24" :md="12">
      <h5 class="text-h5">Штаб компании:</h5>
      <p class="text-large mt-0">{{ company.boss.city ? company.boss.city.name : '-' }} </p>
    </el-col>
    <el-col :span="24" :md="12">
      <h5 class="text-h5">Дата последнего нахождения на портале:</h5>
      <p class="text-large mt-0">{{ moment(company.boss.last_online_datetime).format("DD.MM.YYYY") }} </p>
    </el-col>
  </el-row>
</template>

<script setup lang="ts">
import moment from "moment";
const props = defineProps(['company']);
</script>

<style scoped>

</style>