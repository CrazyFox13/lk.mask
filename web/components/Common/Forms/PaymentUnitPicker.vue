<template>
  <div v-bind:class="{'input-error':!!error}">
    <el-select :placeholder="placeholder" v-model="value">
      <el-option
          v-for="unit in paymentUnits"
          :label="unit.name"
          :value="unit.id"
      />
    </el-select>
    <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, watch} from "#imports";

const props = defineProps(['modelValue', 'placeholder', 'error', 'vehicleTypeId']);
const value = ref(props.modelValue);
const emits = defineEmits(['update:modelValue']);
const paymentUnits = ref<any[]>([]);
const typeId = ref<number>(props.vehicleTypeId);

const getUnits = async () => {
  if (!props.vehicleTypeId) return;
  const data = await apiFetch(`payment-units?vehicle_type_id=${typeId.value}`);
  paymentUnits.value = data.paymentUnits;
  value.value = props.modelValue;
};

await getUnits();

watch(value, () => {
  emits('update:modelValue', value.value);
});

watch(props, () => {
  value.value = props.modelValue;
  typeId.value = props.vehicleTypeId;
}, {deep: true});

watch(typeId, () => {
  getUnits();
})

</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
}
</style>