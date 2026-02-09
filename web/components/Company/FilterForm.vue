<template>
  <div>
    <div class="filter-title mb-10 mt-20">Выбранный регион</div>
    <RegionPicker v-model="value.cities_id"/>

    <div class="filter-title mb-10 mt-30">Рейтинг</div>
    <RatingPicker v-model="value.rating"/>
  </div>
</template>

<script setup lang="ts">
import RegionPicker from "../Common/Forms/RegionPicker.vue";
import {ref, watch} from "#imports";
import RatingPicker from "~/components/Common/Forms/RatingPicker.vue";
import {type ICompanyFilter} from "~/stores/company";

const emit = defineEmits(['update:modelValue']);
const props = defineProps(['modelValue']);

const value = ref<ICompanyFilter>(props.modelValue);

watch(value, () => {
  emit('update:modelValue', value.value);
}, {deep: true});

watch(props, () => {
  value.value = props.modelValue;
}, {deep: true})

</script>

<style scoped>

</style>