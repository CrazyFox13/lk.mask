<template>
  <div>
    <TextInput
        placeholder="Выберите регион"
        @focusin="onInputActive"
        v-model="readOnlyValue"
    >
      <template #suffix>
        <SvgIcon name="chevron-down"/>
      </template>
    </TextInput>
    <CityTreeSelector
        key="cityTree"
        v-if="dialog"
        :items="items"
        :regions-id="selectedRegionsId"
        @close="onDialogClose"
        @regionsUpdate="onRegionsUpdate"
        @search="getRegions"
        v-model="selectedCities"
    />

  </div>
</template>

<script setup lang="ts">

import {apiFetch} from "~/composables/apiFetch";
import {computed, nextTick, onMounted, useAsyncData, watch} from "#imports";
import CityTreeSelector from "~/components/Common/Forms/CityTreeSelector.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import TextInput from "~/components/Common/Forms/TextInput.vue";

const emit = defineEmits(['update:modelValue']);
const props = defineProps(['modelValue', 'regions_id']);

const selectedRegionsId = ref<number[]>(props.regions_id ? props.regions_id : []);
const selectedCities = ref<any[]>([]);
const value = ref<number[]>(props.modelValue ? props.modelValue : []);
const regions = ref<any[]>([])
const dialog = ref(false);
const readOnlyValue = ref('');

const getRegions = async () => {
  const data = await apiFetch('geo-regions?take=-1&with_orders=1&city=');
  regions.value = data.regions;
  await nextTick(() => {
    init();
  })
}
useAsyncData(() => getRegions())

const onInputActive = () => {
  dialog.value = true;
}

const items = computed(() => {
  return regions.value ? regions.value.map((reg: any) => {
    reg.key = `region-${reg.id}`;
    reg.title = reg.name_with_type;
    reg.cities = reg.cities.map((ct: any) => {
      ct.key = `city-${ct.id}`
      ct.title = ct.name;
      return ct;
    })
    return reg;
  }) : [];
})


watch(selectedCities, (cities) => {
  value.value = cities.map((c: any) => c.id).filter((v, i, self) => self.indexOf(v) === i);
  readOnlyValue.value = cities.map(c => c.name).filter((v, i, self) => self.indexOf(v) === i).join(", ")
}, {deep: true});


watch(selectedRegionsId, (regionsId: number[], oldRegionsId: number[]) => {
  const removedRegions = oldRegionsId.filter((r: number) => !regionsId.includes(r));
  removedRegions.forEach((regionId: number) => {
    const region = items.value.find((r: any) => r.id === regionId && r.key.includes("region"));
    const citiesId = region.cities.map((c: any) => c.id);
    value.value = value.value.filter((cityId: number) => !citiesId.includes(cityId));
  });

  const addedRegions = regionsId.filter((r: number) => !oldRegionsId.includes(r));
  addedRegions.forEach((regionId: number) => {
    const region = items.value.find((r: any) => r.id === regionId && r.key.includes("region"));
    const citiesId = region.cities.map((c: any) => c.id);
    value.value = [...value.value, ...citiesId];
  });

  makeCitiesUnique();
});


watch(value, (newOne, old) => {

  if (JSON.stringify(old) !== JSON.stringify(newOne)) {
    emit('update:modelValue', value.value)
  }
}, {deep: true})


watch(props, () => {
  value.value = props.modelValue;
  init();
}, {deep: true})

const init = () => {
  if (props.modelValue.length === 0) {
    // reset?
    if (selectedRegionsId.value.length > 0) {
      selectedRegionsId.value = [];
    }
    if (selectedCities.value.length > 0) {
      selectedCities.value = [];
    }
  } else {
    regions.value.forEach(region => {
      region.cities.forEach((city: any) => {
        const cityNeedToBeSelected = value.value.includes(city.id)
        const cityActuallySelectedIndex = selectedCities.value.findIndex(s => s.id === city.id);
        if (cityNeedToBeSelected && cityActuallySelectedIndex === -1) {
          selectedCities.value.push(city);
        } else if (cityActuallySelectedIndex >= 0 && !cityNeedToBeSelected) {
          selectedCities.value.splice(cityActuallySelectedIndex, 1);
        }
      });
      const selectedInRegion = selectedCities.value.filter((c: any) => c.geo_region_id === region.id).length;
      if (selectedInRegion > 0 && region.cities.length === selectedInRegion) {
        selectedRegionsId.value.push(region.id);
      } else {
        const idx = selectedRegionsId.value.findIndex((id: number) => id === region.id);
        if (idx >= 0) {
          selectedRegionsId.value.splice(idx, 1);
        }
      }
    })
  }
}

const onRegionsUpdate = (regs: number[]) => {
  selectedRegionsId.value = regs;
}

const makeCitiesUnique = () => {
  selectedCities.value = selectedCities.value.filter((i: any, k: number, self: any[]) => self.indexOf(i) === k);
}

const onDialogClose = () => {
  dialog.value = false;
}
</script>

<style scoped>

</style>