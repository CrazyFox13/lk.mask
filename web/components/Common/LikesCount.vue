<template>
  <div class="text-hint text-orange like-container">
    <SvgIcon width="11px" height="11px" name="like"/>
    {{ props.likes }}
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";

const props = defineProps(['likes']);
</script>

<style lang="scss">
.like-container {
  display: flex;
  align-items: center;

  span {
    margin-right: 1px;
  }
}
</style>