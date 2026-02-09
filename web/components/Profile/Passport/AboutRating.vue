<template>
  <el-card>
    <div class="flex align-items-center">
      <RatingStars class="mr-5" :rating="company.rating" :size="25" :col-gap="5"/>
      <h2 class="text-h2 mb-0">{{ company.rating }} баллов</h2>
    </div>
    <div class="text-h4">Из чего состоит рейтинг</div>
    <div class="flex flex-column">
      <RatingDetailItem class="mb-25" v-for="(detail,k) in ratingDetails" :key="k" :detail="detail"/>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import RatingStars from "~/components/Common/RatingStars.vue";
import RatingDetailItem from "~/components/Profile/Passport/RatingDetailItem.vue";

defineProps(['company', 'ratingDetails']);
</script>

<style scoped lang="scss">
.text-h4 {
  margin-top: 20px;
  margin-bottom: 20px;
  @media (min-width: 992px) {
    margin-top: 40px;
    margin-bottom: 30px;
  }
}
</style>