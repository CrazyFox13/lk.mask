<template>
  <div v-bind:class="{'input-error':!!error}">
    <div class="mt-15" v-if="read_only">{{ value }}</div>
    <el-input
        v-else-if="mask"
        v-bind:class="{'white-shadow':white}"
        :disabled="disabled"
        v-maska
        :data-maska="mask"
        v-model="value"
        :placeholder="placeholder"
        autocomplete="none"
    />
    <el-input
        v-else
        v-bind:class="{'white-shadow':white}"
        :disabled="disabled"
        v-model="value"
        :placeholder="placeholder"
        :type="type?type:'text'"
        autocomplete="none">
      <template #suffix>
        <slot name="suffix"/>
      </template>
    </el-input>
    <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import {watch} from "#imports";

const props = defineProps(['modelValue', 'placeholder', 'error', 'mask', 'type', 'disabled','read_only', 'white']);
const value = ref(props.modelValue);
const emits = defineEmits(['update:modelValue']);
watch(value, () => {
  emits('update:modelValue', value.value);
});

watch(() => props.modelValue, (newVal) => {
  value.value = newVal;
});

</script>

<style lang="scss">
.white-shadow {
  .el-input__wrapper {
    background: white;
    filter: drop-shadow(4px 4px 25px rgba(36, 34, 34, 0.15));
    font-family: "Roboto Regular", sans-serif;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px;
    .el-input__inner::placeholder {
      color: black;

    }
  }
}
</style>
