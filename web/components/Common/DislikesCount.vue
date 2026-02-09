<template>
  <div class="text-hint text-dark-gray dislike-container">
    <SvgIcon width="11px" height="11px" name="dislike" class="text-gray"/>
    {{ props.dislikes }}
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";

const props = defineProps(['dislikes']);
</script>

<style lang="scss">
.dislike-container {
  display: flex;
  align-items: center;

  span {
    margin-right: 1px;
  }
}
</style>