<template>
  <div>
    <AddressPicker
        v-for="(address,i) in value"
        :key="`${i}-${address.address}`"
        :index="i"
        @deleted="onDeletedAddress(address)"
        v-model="value[i]"
    />

    <el-button link @click="addAddress()" v-if="value.length<markerIndexes.length">
      <SvgIcon
          name="circle-plus"
          class="mr-2"
      />
      Добавить еще адрес
    </el-button>
    <client-only>
      <OrderMap
          :key="`${vehicleGroupId}`"
          v-if="mapLoaded && !rendering"
          class="mt-20"
          :addresses="selectedAddresses"
          :vehicleGroupId="vehicleGroupId"
      />
    </client-only>
    <p class="text-hint text-error" v-if="!!errors.addresses">{{ errors.addresses }}</p>
  </div>
</template>

<script lang="ts" setup>
import AddressPicker from "~/components/Common/Forms/AddressPicker.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import {computed, nextTick, ref, watch} from "#imports";
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";
import {useOrderStore} from "~/stores/order";
import OrderMap from "~/components/Common/OrderMap.vue";
import type {IAddress} from "~/types/address";

const authStore = useAuthStore();
const {markerIndexes} = storeToRefs(useOrderStore())
const props = defineProps(['modelValue', 'errors','vehicleGroupId']);
const emit = defineEmits(['update:modelValue']);
const mapLoaded = ref(!!authStore.geoCity);
const coordinates = ref([authStore.geoCity ? authStore.geoCity.lat : 55, authStore.geoCity ? authStore.geoCity.lng : 33]);
const value = ref<IAddress[]>(props.modelValue ? props.modelValue : [
  {
    address: '',
    lat: coordinates.value[0],
    lng: coordinates.value[1],
  }
]);
const rendering = ref(false);

authStore.$subscribe((mutation, state) => {
  mapLoaded.value = !!state.geoCity;
  if (mapLoaded.value) {
    coordinates.value = [state.geoCity!.lat, state.geoCity!.lng];
  }
}, {detached: true});

const selectedAddresses = computed(() => {
  return value.value.filter((v: IAddress) => v.fias_id || v.geo_city_id);
})

watch(value, () => {
  rendering.value = true;
  nextTick(() => {
    rendering.value = false;
  })
  emit('update:modelValue', selectedAddresses.value);
}, {deep: true});

const onDeletedAddress = (address: IAddress) => {
  const i = selectedAddresses.value.indexOf(address);
  value.value.splice(i, 1);
}

const addAddress = () => {
  value.value.push({
    address: '',
    lat: coordinates.value[0],
    lng: coordinates.value[1],
    city: undefined,
    geo_city_id: undefined,
    fias_id: undefined,
    region: undefined,
    geo_region_id: undefined,
    region_fias_id: undefined
  })
}

if (value.value.length === 0) {
  addAddress();
}
</script>

<style scoped>

</style>