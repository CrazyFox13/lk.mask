<template>
  <div>
    <el-card>
      <div class="offers" @click="onClick()">
        <OrderOffersCount class="offers-text" :order="order"/>
        <SvgIcon name="chevron-right"/>
      </div>
    </el-card>
  </div>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";
import BadgeLabel from "~/components/Common/BadgeLabel.vue";
import OrderOffersCount from "~/components/Orders/OrderOffersCount.vue";

const props = defineProps(['order']);
const emit = defineEmits(["open"]);

const onClick = () => {
  emit("open");
}
</script>

<style scoped lang="scss">
.offers {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;

  &-text {
    display: flex;
    align-items: center;
  }
}
</style>