<template>
  <div v-bind:class="{'input-error':!!error}">
    <el-select :placeholder="placeholder" v-model="value">
      <el-option
          v-for="type in companyTypes"
          :label="type.title"
          :value="type.id"
      />
    </el-select>
    <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, watch, computed} from "#imports";

const props = defineProps(['modelValue', 'placeholder', 'error']);
const value = ref(props.modelValue);
const emits = defineEmits(['update:modelValue']);

const {companyTypes: allCompanyTypes} = await apiFetch('company-types');

// Фильтруем тип "Заказчик-Поставщик" на фронтенде тоже
const companyTypes = computed(() => {
  return allCompanyTypes.filter((type: any) => {
    const title = type.title || '';
    return title !== 'Заказчик-Поставщик' 
        && !title.includes('Заказчик-Поставщик')
        && title !== 'Заказчик - Поставщик'
        && title !== 'Заказчик/Поставщик';
  });
});

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