<template>
  <div>
    <div v-if="approvedCompanyExists">
      <about-company :company="company"/>
      <about-rating class="mt-30" :company="company" :rating-details="ratingDetails"/>
    </div>
    <NoApprovedCompany v-else/>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, computed, ref, useRoute} from "#imports";
import AboutCompany from "~/components/Profile/Passport/AboutCompany.vue";
import AboutRating from "~/components/Profile/Passport/AboutRating.vue";
import {storeToRefs} from "pinia";
import {type ICompany, useAuthStore} from "~/stores/user";
import NoApprovedCompany from "~/components/Profile/NoApprovedCompany.vue";

const {noApprovedCompany} = useAuthStore();
const {user} = storeToRefs(useAuthStore());

const approvedCompanyExists = computed(() => !noApprovedCompany());

const company = ref<ICompany | undefined>(undefined)
const ratingDetails = ref<any | undefined>(undefined)

if (approvedCompanyExists.value) {
  const response = await apiFetch(`companies/${user.value!.company_id}/passport`);
  company.value = response.company;
  ratingDetails.value = response.ratingDetails;
}
</script>

<style scoped lang="scss">

</style>