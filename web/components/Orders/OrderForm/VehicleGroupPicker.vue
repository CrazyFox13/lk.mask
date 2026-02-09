<template>
  <div>
    <label class="input-title">Категория</label>
    <el-select v-model="value" placeholder="Не выбрано">
      <el-option
          v-for="group in groups"
          :key="group.id"
          :label="group.title"
          :value="group.id"
      />
    </el-select>
  </div>
</template>

<script setup lang="ts">
import {watch} from "#imports";

const props = defineProps(['modelValue', 'groups']);
const emit = defineEmits(['update:modelValue']);
const value = ref(props.modelValue);

watch(value, () => {
  emit('update:modelValue', value.value);
})
</script>

<style scoped>

</style>