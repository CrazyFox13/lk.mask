<template>
  <span class="notification-badge" v-if="value">{{ value }}</span>
</template>

<script setup lang="ts">
defineProps(['value']);
</script>

<style scoped lang="scss">

.notification-badge {
  font-family: "Roboto Bold", sans-serif;
  font-style: normal;
  font-weight: 700;
  background: #F92609;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  margin-left: 5px;
  font-size: 8px;
  line-height: 7px;
  width: 15px;
  height: 15px;

  @media (min-width: 992px) {
    font-size: 12px;
    line-height: 8px;
    width: 20px;
    height: 20px;
  }
}
</style>