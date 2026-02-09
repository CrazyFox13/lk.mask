<template>
  <div class="flex align-items-center">
    {{ map[status]["label"] }}&nbsp;<SvgIcon v-if="icon" :name="icon"/>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {computed} from "#imports";

const props = defineProps(['status']);
type StatusType = "draft" | "moderation" | "on_approval" | "approved" | "in_progress" | "canceled" | "removed" | "completed";
const map = {
  "draft": {
    icon: "",
    label: "Черновик"
  }, // черновик
  "moderation": {
    icon: "clock",
    label: "На модерации"
  }, // на модерации
  "on_approval": {
    icon: "clock",
    label: "На согласовании"
  }, // на согласовании
  "approved": {
    icon: "report-confirmed",
    label: "Новая заявка"
  }, // новая заявка
  "in_progress": {
    icon: "in-progress",
    label: "В работе"
  }, // в работе
  "canceled": {
    icon: "form-uncheck",
    label: "Отменённая"
  }, // отменённая
  "removed": {
    icon: "remove-list",
    label: "Снято с публикации"
  }, // снято с публикации
  "completed": {
    icon: "",
    label: "Завершённая"
  }, // завершённая
};

const icon = computed(() => {
  return map[<StatusType>props.status]["icon"]
})
</script>

<style scoped lang="scss">

</style>