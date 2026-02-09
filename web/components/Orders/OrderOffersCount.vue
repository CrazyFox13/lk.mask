<script setup lang="ts">

import BadgeLabel from "~/components/Common/BadgeLabel.vue";
import SvgIcon from "~/components/SvgIcon.vue";

defineProps({
  order: Object as () => any,
})
</script>

<template>
  <div class="flex align-items-center">
    <SvgIcon width="18px" name="offers" class="mr-2">
      <template #badge>
        <BadgeLabel :value="order.new_offers_count"/>
      </template>
    </SvgIcon>
    <div>
      {{ order.offers_count }} Предложений
    </div>
  </div>
</template>

<style scoped lang="scss">

</style>