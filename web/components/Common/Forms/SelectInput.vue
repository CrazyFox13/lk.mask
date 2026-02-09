<template>
  <div v-bind:class="{'input-error':!!error}">
    <el-select :placeholder="placeholder" v-model="value">
      <el-option
          v-for="option in options"
          :label="option.label"
          :value="option.value"
      />
    </el-select>
    <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import {watch} from "#imports";

const props = defineProps(['modelValue', 'placeholder', 'error', 'options']);
const value = ref(props.modelValue);
const emits = defineEmits(['update:modelValue']);
watch(value, () => {
  emits('update:modelValue', value.value);
});

watch(props, () => {
  value.value = props.modelValue;
});

</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
}
</style>