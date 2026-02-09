<template>
  <div class="page">
    <div class="container">
      <div class="flex gap-2 items-center el-breadcrumb">
        <nuxt-link class="is-link" :to="{ path: '/' }">Главная</nuxt-link>
        <el-icon class="separator"><ArrowRight></ArrowRight></el-icon>
        <nuxt-link class="is-link" :to="{ path: '/companies' }">Список исполнителей</nuxt-link>
        <el-icon class="separator"><ArrowRight></ArrowRight></el-icon>
        <nuxt-link class="is-link inactive">{{ company.title }}</nuxt-link>
      </div>

      <TabLinks :company-id="companyId"/>
      <el-row>
        <el-col :span="24" :md="8">
          <CompanyContactsCard
              :company="company"
              @recommendation="recommendationDialog=true"
              @report="reportDialog=true"
          />
        </el-col>
        <el-col :span="24" :md="16">
          <NuxtPage class="mt-20" :company="company"/>
        </el-col>
      </el-row>

      <Adv key="companies_item" width="765" height="130" :banner="findBanner(banners, 'companies_item')"
           class="hidden-sm-and-down mt-40"/>

      <YaAdv ya-id="yandex_rtb_R-A-6812315-7" block-id="R-A-6812315-7" :width="765" :height="130"
             class="hidden-sm-and-down mt-20"/>


      <CreateRecommendationDialog
          v-if="recommendationDialog"
          @close="recommendationDialog=false"
          :company="company"
      />
      <CreateReportDialog
          v-if="reportDialog"
          @close="reportDialog=false"
          :company="company"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, findBanner, ref, useRoute} from "#imports";
import {ArrowRight} from '@element-plus/icons-vue';
import TabLinks from "~/components/Company/TabLinks.vue";
import CompanyContactsCard from "~/components/Company/CompanyContactsCard.vue";
import CreateRecommendationDialog from "~/components/Company/CreateRecommendationDialog.vue";
import CreateReportDialog from "~/components/Company/CreateReportDialog.vue";
import Adv from "~/components/Adv.vue";
import YaAdv from "~/components/Adv/YaAdv.vue";

const route = useRoute();

const companyId = Number(route.params.id);
const {company} = await apiFetch(`companies/${companyId}`);
const recommendationDialog = ref(false);
const reportDialog = ref(false);

const vehicleTypes = company.vehicle_groups.map((gr: any) => gr.types.map((t: any) => t.id)).flat()
const {banners} = await apiFetch(`adv?places=companies_item&vehicle_types_id=${vehicleTypes}`);
</script>

<style scoped lang="scss">
.el-col {
  margin-top: 20px;
  @media (min-width: 992px) {
    margin-top: 0;
    &:first-of-type {
      padding-left: 20px;
      order: 2;
    }
    &:last-of-type {
      padding-right: 20px;
      order: 1;
    }

  }
}
</style>