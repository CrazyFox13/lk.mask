<template>
  <div v-bind:class="{'input-error':!!error}">
    <label class="input-title">Подкатегория</label>
    <div>
      <el-select v-model="value" placeholder="Не выбрано">
        <el-option
            v-for="type in types"
            :key="type.id"
            :label="type.title"
            :value="type.id"
        />
      </el-select>
      <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
    </div>
  </div>
</template>

<script lang="ts" setup>
import {watch} from "#imports";

const props = defineProps(['modelValue', 'types', 'error']);
const emit = defineEmits(['update:modelValue']);
const value = ref(props.modelValue);

watch(value, () => {
  emit('update:modelValue', value.value);
})
</script>

<style scoped lang="scss">
.el-select{
  width: 100%;
}
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