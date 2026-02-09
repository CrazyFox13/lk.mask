<template>
  <div class="flex align-items-center text-sub-subtitle subtext-dates">
    <SvgIcon name="clock" class="mr-2"/>
    c {{ moment(order.start_date).format("DD.MM.YY") }} на {{
      shiftsCount(order.start_date, order.finish_date)
    }}
    смены
  </div>
</template>

<script lang="ts" setup>

import {useOrderStore} from "~/stores/order";
import moment from "moment";
import SvgIcon from "~/components/SvgIcon.vue";

const props = defineProps(['order']);
const {shiftsCount} = useOrderStore();
</script>

<style scoped lang="scss">
.subtext-dates {
  width: 100%;
  margin-bottom: 8px;

  span {
    display: flex;
    align-items: center;
  }

  @media (min-width: 992px) {
    width: auto;
    margin-bottom: 0;
  }
}
</style>