<template>
  <div v-bind:class="{'input-error':!!error}">
    <el-date-picker
        class="date-picker"
        type="date"
        :placeholder="placeholder"
        :size="'default'"
        v-model="value"
        :format="format"
    />
    <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
  </div>
</template>

<script setup>
import {watch} from "#imports";

const props = defineProps(['placeholder', 'modelValue','format','error']);
const emit = defineEmits(['update:modelValue']);
const value = ref(props.modelValue);

watch(value, () => {
  emit('update:modelValue', value.value)
});

watch(props, () => {
  value.value = props.modelValue;
}, {deep: true})
</script>

<style lang="scss">
.el-date-editor .el-input__wrapper{
  height: 40px !important;
}
</style>