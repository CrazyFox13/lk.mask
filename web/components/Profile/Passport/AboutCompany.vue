<template>
  <el-card>
    <div class="flex">
      <el-image class="avatar hidden-sm-and-down mr-5" :src="company.boss.avatar"/>
      <div style="width: 100%">
        <div class="flex justify-between flex-column flex-md-row">
          <div>
            <p class="text-gray mt-0 mb-10">Код №{{ company.viewable_reg_number }}</p>
            <div class="text-h3 title-with-icon">
              <SvgIcon name="verification" v-if="company.verified"/>
              {{ company.title }}
            </div>
          </div>
          <AwardList
              :company="company"
          />
        </div>
        <div class="flex flex-column flex-md-row mt-10" style="row-gap: 5px">
          <p class="mb-0 mt-0 mr-3 flex align-items-center">
            <SvgIcon name="form-check" class="mr-2"/>
            Утверждена модератором
          </p>
          <p class="mb-0 mt-0 flex align-items-center" v-if="company.self_park">
            <SvgIcon name="form-check" class="mr-2"/>
            Собственный подтверженный парк
          </p>
        </div>
      </div>
    </div>
    <el-divider/>
    <CompanyDatesBlock :company="company"/>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import moment from "moment";
import CompanyDatesBlock from "~/components/Company/CompanyDatesBlock.vue"
import AwardList from "~/components/Company/AwardList.vue"
const props = defineProps(['company']);
</script>

<style scoped lang="scss">
.avatar {
  min-width: 116px;
  width: 116px;
  height: 116px;
  border-radius: 50%;
}
</style>