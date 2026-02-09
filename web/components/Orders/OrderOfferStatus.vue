<template>
  <div class="flex align-items-center">
    {{ map[status]["label"] }}&nbsp;<SvgIcon width="14px" :class="map[status]['class']" v-if="icon" :name="icon"/>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {computed} from "#imports";

const props = defineProps(['status']);
type StatusType = "waiting" | "accepted" | "declined";
const map = {
  "waiting": {
    icon: "clock",
    label: "На рассмотрении",
    class: "text-gray"
  }, // на модерации
  "accepted": {
    icon: "check-circle",
    label: "Предложение одобрено",
    class: "text-orange"
  }, // одобрено
  "declined": {
    icon: "cancel",
    label: "Предложение отклонено",
    class: "text-gray"
  }, // отклонено
};

const icon = computed(() => {
  return map[<StatusType>props.status]["icon"]
})
</script>

<style scoped lang="scss">

</style>