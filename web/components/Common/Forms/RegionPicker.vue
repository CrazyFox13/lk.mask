<template>
  <div>
    <el-checkbox-group v-model="selectedRegionsId" class="flex flex-column align-items-start">
      <el-checkbox :key="region.id" v-for="region in topRegions" :label="region.id">
        {{ region.name_with_type }}
      </el-checkbox>
      <el-link @click="dialog=true" class="mt-10" type="primary">Показать все</el-link>
    </el-checkbox-group>

    <CityTreeSelector
        key="cityTree"
        v-if="dialog"
        :items="items"
        :regions-id="selectedRegionsId"
        @close="onDialogClose"
        @regionsUpdate="onRegionsUpdate"
        v-model="selectedCities"
    />

  </div>
</template>

<script setup lang="ts">

import {apiFetch} from "~/composables/apiFetch";
import {computed, nextTick, onMounted, useAsyncData, watch} from "#imports";
import CityTreeSelector from "~/components/Common/Forms/CityTreeSelector.vue";

const emit = defineEmits(['update:modelValue']);
const props = defineProps(['modelValue', 'regions_id']);

const selectedRegionsId = ref<number[]>(props.regions_id ? props.regions_id : []);
const selectedCities = ref<any[]>([]);
const value = ref<number[]>(props.modelValue ? props.modelValue : []);
const regions = ref<any[]>([])
const dialog = ref(false);

const getRegions = async () => {
  const data = await apiFetch('geo-regions?with_orders=1&city=');
  regions.value = data.regions;
}
useAsyncData(() => getRegions())

const topRegions = computed(() => {
  return regions.value ? [...regions.value].splice(0, 3) : [];
});

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
  value.value = cities.map((c: any) => c.id);
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


onMounted(() => {
  init();
})

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
  value.value = value.value.filter((i: any, k: number, self: any[]) => self.indexOf(i) === k);
}

const onDialogClose = () => {
  dialog.value = false;
}
</script>

<style scoped>

</style>