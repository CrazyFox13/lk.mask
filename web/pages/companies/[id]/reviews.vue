<template>
  <div>
    <div class="view-switcher">
      <nuxt-link
          @click="view='recommendations'"
          class="el-link"
          :to="`/companies/${companyId}/reviews?tab=recommendations`"
          v-bind:class="{'active':view==='recommendations'}">
        <LikeIcon size="15" class="mr-2"/>
        Рекомендации
      </nuxt-link>
      <nuxt-link
          @click="view='reports'"
          class="el-link"
          :to="`/companies/${companyId}/reviews?tab=reports`"
          v-bind:class="{'active':view==='reports'}"
      >
        <DislikeIcon size="15" class="mr-2"/>
        Претензии
      </nuxt-link>
    </div>
    <CompanyRecommendations
        v-if="view==='recommendations'"
    />
    <CompanyReports
        v-else-if="view==='reports'"
    />
  </div>
</template>

<script setup lang="ts">
import CompanyRecommendations from "~/components/Company/CompanyRecommendations.vue";
import CompanyReports from "~/components/Company/CompanyReports.vue";
import LikeIcon from "~/components/Common/Icons/LikeIcon.vue";
import DislikeIcon from "~/components/Common/Icons/DislikeIcon.vue";
import {useRoute} from "#imports";

const route = useRoute();
const companyId = Number(route.params.id);
const view = ref<"reports" | "recommendations">(route.query.tab === "reports" ? "reports" : "recommendations");

</script>

<style lang="scss">
.view-switcher {
  display: flex;
  column-gap: 30px;
  margin-top: 30px;
  margin-bottom: 24px;

  .el-link {
    padding-bottom: 5px;

    &.active {
      border-bottom: 2px solid #EB8A00;
    }
  }


}
</style>