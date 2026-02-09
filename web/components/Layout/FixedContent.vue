<template>
  <div class="fixed-content">
    <slot name="content"/>
  </div>
</template>

<script setup lang="ts">
</script>

<style scoped lang="scss">
.fixed-content {
  position: fixed;
  bottom: 65px;
  width: 100%;
  left: 0;
  filter: drop-shadow(0px -2px 15px rgba(0, 0, 0, 0.07));
  padding-top: 0;
  padding-bottom: 8px;
  z-index: 2;
}
</style>