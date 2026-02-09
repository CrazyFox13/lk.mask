<template>
  <el-card>
    <div class="flex mb-20">
      <el-avatar
          :alt="report.company.title"
          :src="report.author.avatar?report.author.avatar:DefaultAvatar"
      />
      <div class="flex flex-column">
        <div class="text-h4 title-with-icon">
          <SvgIcon name="verification" v-if="report.company.verified"/>
          {{ report.company.title }}
        </div>
        <p>{{ report.author.name }} {{ report.author.surname }}</p>
      </div>
    </div>
    <div class="flex report-info">
      <div class="flex align-items-center">
        <SvgIcon name="report-type" class="mr-2"/>
        <b>Тема претензии: {{ report.type ? report.type.title : '-' }}</b>
      </div>
      <div class="flex align-items-center">
        <SvgIcon name="payment" class="mr-2"/>
        <b>Сумма претензии: {{ formatPrice(report.amount) }}</b>
      </div>
    </div>
    <p class="mt-10" v-html="short ? truncate(report.text, maxShortLength) : report.text"></p>
    <div class="flex mt-20 referee" v-if="report.referee_conclusion">
      <div class="referee-avatar">
        <el-avatar alt="referee" :src="RefereeAvatar"/>
        <p class="text-hint">Арбитр</p>
      </div>
      <div class="flex">
        <SvgIcon name="referee" class="mr-2 text-orange"/>
        <p v-html="short ? truncate(report.referee_conclusion, maxShortLength) : report.referee_conclusion"></p>
      </div>
    </div>
    <el-link type="primary" class="mt-10" v-if="isLong" @click="short=!short">
      {{ short ? 'Читать далее' : 'Свернуть' }}
    </el-link>
    <div class="flex align-items-center mt-20">
      <p class="text-subtitle text-gray mr-10">{{ ruMoment(report.created_at).fromNow() }}</p>
      <p v-if="status" class="flex align-items-center">
        <SvgIcon :name="status.icon" v-if="status.icon" class="mr-1"/>
        {{ status.label }}
      </p>
    </div>
  </el-card>
</template>

<script lang="ts" setup>
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import RefereeAvatar from "~/assets/images/referee_avatar.png";
import SvgIcon from "~/components/SvgIcon.vue";
import {truncate, formatPrice, computed, ruMoment} from "#imports";
import {useReportStore} from "~/stores/report";

const props = defineProps(['report']);
const maxShortLength = 300;
const isLong = props.report.text.length > maxShortLength;
const short = ref(true);

const reportStore = useReportStore();
const {getStatusByKey} = reportStore;
const status = computed(() => {
  return getStatusByKey(props.report.status);
})
</script>

<style scoped lang="scss">
.el-avatar {
  margin-right: 16px;
}

.report-info {
  flex-wrap: wrap;
  row-gap: 10px;
  @media (min-width: 992px) {
    column-gap: 30px;
  }
}

.referee {
  background: #E8EBF1;
  border-radius: 6px;
  padding: 16px 20px;

  .referee-avatar {
    margin-right: 25px;
    text-align: center;
    justify-content: center;
    align-items: center;

    .el-avatar {
      margin: 0;
      width: 35px;
      height: 35px;
    }
  }
}

.text-h4, p {
  margin: 0;
}
</style>