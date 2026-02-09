<template>
  <el-select
      :placeholder="placeholder"
      v-model="value"
  >
    <el-option
        v-for="type in reportTypes"
        :key="type.id"
        :value="type.id"
        :label="type.title"
    >
      {{ type.title }}
    </el-option>
  </el-select>
</template>

<script setup lang="ts">

import {apiFetch, ref, watch, watchEffect} from "#imports";

const {reportTypes} = await apiFetch(`report-types`, {}, true);

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: {
    type: Number,
    default: undefined,
  },
  placeholder: {
    type: String,
    default: "Выберите тип"
  }
});

const value = ref<number>();

watchEffect(() => value.value = props.modelValue);

watch(value, (v) => {
  emit('update:modelValue', v);
})
</script>

<style scoped>

</style>