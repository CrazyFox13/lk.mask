<template>
  <div v-bind:class="{'input-error':!!error}">
    <label class="input-title">Количество требуемой техники</label>
    <div>
      <el-input-number :min="1" v-model="value"/>
      <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {watch} from "#imports";

const props = defineProps(['modelValue', 'error']);
const emit = defineEmits(['update:modelValue']);
const value = ref<number>(props.modelValue);
watch(value, () => {
  emit('update:modelValue', value.value);
})
</script>

<style scoped lang="scss">
.input-error {
  position: relative;

  .el-input {
    border: 1px solid #F92609;
  }

  p {
    font-size: 10px;
    line-height: 11px;
    margin: 5px 0 10px;
    position: static;
  }
}
</style>