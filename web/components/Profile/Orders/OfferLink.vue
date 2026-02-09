<script setup lang="ts">

import BadgeLabel from "~/components/Common/BadgeLabel.vue";
import SvgIcon from "~/components/SvgIcon.vue";

defineProps({
  order: {
    type: Object as () => any,
    required: true
  }
})
</script>

<template>
  <nuxt-link :to="`/profile/orders/${order.id}/offers`" class="mr-2 el-button el-button--info el-button--small">
    <SvgIcon width="18px" name="offers" class="mr-2">
      <template #badge>
        <BadgeLabel :value="order.new_offers_count"/>
      </template>
    </SvgIcon>
    <div class="mr-2">
      {{ order.offers_count }} Предложений
    </div>
    <SvgIcon name="chevron-right"/>
  </nuxt-link>
</template>

<style scoped lang="scss">

</style>