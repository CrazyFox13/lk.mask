<template>
  <div>
    <el-card>
      <AwardList
          :company="companyDetails"
      />
      <el-divider v-if="companyDetails.awards.length>0"/>

      <!--<div>SELF PARK</div>
      <el-divider/>-->

      <CompanyDatesBlock :company="companyDetails"/>
    </el-card>

    <about-rating class="mt-30" :company="companyDetails" :rating-details="ratingDetails"/>
  </div>
</template>

<script setup lang="ts">
import {apiFetch} from "../../../composables/apiFetch";
import AboutCompany from "~/components/Profile/Passport/AboutCompany.vue";
import AboutRating from "~/components/Profile/Passport/AboutRating.vue";
import CompanyDatesBlock from "~/components/Company/CompanyDatesBlock"
import AwardList from "~/components/Company/AwardList.vue"

const props = defineProps(['company']);
const body = await apiFetch(`companies/${props.company.id}/passport`);
const ratingDetails = body.ratingDetails;
const companyDetails = body.company;
</script>

<style scoped lang="scss">

</style>