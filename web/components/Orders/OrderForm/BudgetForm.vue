<template>
  <div>
    <div class="input-group" v-if="!value.amount_by_agreement">
      <label class="input-title">Стоимость за</label>
      <PaymentUnitPicker
          placeholder="Не выбрано"
          v-model="value.payment_unit_id"
          :error="errors.payment_unit_id"
          :vehicle-type-id="vehicleTypeId"
      />
    </div>

    <div class="input-group" v-if="!value.amount_by_agreement">
      <label class="input-title">Безналичный расчет с НДС</label>
      <TextInput
          v-model="value.amount_account_vat"
          placeholder="Введите сумму"
          type="number"
          :error="errors.amount_account_vat"
      />
    </div>

    <div class="input-group" v-if="!value.amount_by_agreement">
      <label class="input-title">Безналичный расчет без НДС</label>
      <TextInput
          v-model="value.amount_account"
          placeholder="Введите сумму"
          type="number"
          :error="errors.amount_account"
      />
    </div>

    <div class="input-group" v-if="!value.amount_by_agreement">
      <label class="input-title">Наличный расчет</label>
      <TextInput
          v-model="value.amount_cash"
          placeholder="Введите сумму"
          type="number"
          :error="errors.amount_cash"
      />
    </div>
    <div class="input-group">
      <el-switch active-text="Без торга" v-model="value.no_haggling"/>
    </div>
    <div class="input-group">
      <el-switch active-text="По договоренности" v-model="value.amount_by_agreement"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {watch} from "#imports";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import SelectInput from "~/components/Common/Forms/SelectInput.vue";
import PaymentUnitPicker from "~/components/Common/Forms/PaymentUnitPicker.vue";

export interface IOrderBudget {
  payment_unit_id: number
  amount_account_vat: number
  amount_account: number
  amount_cash: number
  amount_by_agreement: boolean
  no_haggling: boolean
}

const props = defineProps(['modelValue', 'errors', 'vehicleTypeId']);
const emit = defineEmits(['update:modelValue']);
const value = ref<IOrderBudget>({
  payment_unit_id: props.modelValue?.payment_unit_id ? props.modelValue.payment_unit_id : undefined,
  amount_account_vat: props.modelValue?.amount_account_vat ? props.modelValue.amount_account_vat : undefined,
  amount_account: props.modelValue?.amount_account ? props.modelValue.amount_account : undefined,
  amount_cash: props.modelValue?.amount_cash ? props.modelValue.amount_cash : undefined,
  amount_by_agreement: !!props.modelValue?.amount_by_agreement,
  no_haggling: !!props.modelValue?.no_haggling,
});

watch(value, () => {
  emit("update:modelValue", value.value)
}, {deep: true});
</script>

<style scoped>

</style>