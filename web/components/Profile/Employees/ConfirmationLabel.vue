<template>
  <div class="flex align-items-center" v-bind:class="{'flex-reverse':reverse}">
    <span v-bind:class="{'hidden-sm-and-down':!permanent}">{{
        confirmed ? 'Подтвержден' : 'Не подтверждён'
      }}&nbsp;</span>
    <SvgIcon :name="confirmed?'form-check':'form-uncheck'"/>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";

defineProps(['confirmed', 'permanent', 'reverse']);
</script>

<style lang="scss" scoped>
.flex-reverse {
  *:first-of-type {
    order: 2;
  }

  *:last-of-type {
    order: 1;
  }
}
</style>