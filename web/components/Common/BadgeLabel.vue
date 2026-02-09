<template>
  <client-only>
    <span class="notification-badge" v-if="value>0">{{ value }}</span>
  </client-only>
</template>

<script setup lang="ts">
defineProps(['value'])
</script>

<style scoped lang="scss">

.notification-badge {
  font-family: "Roboto Bold", sans-serif;
  font-style: normal;
  font-weight: 700;
  font-size: 8px;
  line-height: 7px;
  background: #F92609;
  border-radius: 20px;
  position: absolute;
  right: -4px;
  top: -4px;
  width: 15px;
  height: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  color:white;
}
</style>