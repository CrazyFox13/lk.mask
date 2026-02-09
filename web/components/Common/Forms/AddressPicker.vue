<template>
  <div class="address-picker">
    <el-autocomplete
        v-model="addressQuery"
        placeholder="Введите адрес"
        @select="handleSelect"
        :fetch-suggestions="querySearchAsync"
    >
      <template #prefix>
        <el-icon class="el-input__icon text-orange text-hint">
          <SvgIcon name="empty-marker" margin="-1px 0 0 0">
            <template #content>
              {{ markerIndexes[index] }}
            </template>
          </SvgIcon>
        </el-icon>
      </template>
      <template #suffix>
        <el-button link @click="onDelete">
          <el-icon class="el-input__icon">
            <SvgIcon name="remove-address"/>
          </el-icon>
        </el-button>
      </template>
    </el-autocomplete>
    <el-link type="primary" @click="mapDialog=true">Выбрать на карте</el-link>

    <AddressPickerDialog
        v-if="mapDialog"
        @close="mapDialog=false"
        :query="addressQuery"
        v-model="address"
        v-on:update:modelValue="onUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, ref} from "#imports";
import type {IAddress} from "~/types/address";
import AddressPickerDialog from "~/components/Common/Forms/AddressPickerDialog.vue";
import {storeToRefs} from "pinia";
import {useOrderStore} from "~/stores/order";

const emit = defineEmits(['update:modelValue', 'selected', 'deleted']);
const props = defineProps(['index', 'modelValue']);
const address = ref<IAddress>(props.modelValue);
const addressQuery = ref(props.modelValue.address);
const mapDialog = ref(false);
const {markerIndexes} = storeToRefs(useOrderStore())

const querySearchAsync = async (queryString: string, cb: (arg: any) => void) => {
  if (queryString.length === 0) return;
  const {result} = await apiFetch(`address-from-string?query=${queryString}`);
  cb(result);
}

const handleSelect = (item: any) => {
  address.value = {
    address: item.value,
    lat: Number(item.data.geo_lat),
    lng: Number(item.data.geo_lon),
    city: item.data.city,
    geo_city_id: undefined,
    fias_id: item.data.city_fias_id,
    region: item.data.region,
    geo_region_id: undefined,
    region_fias_id: item.data.region_fias_id
  };
  emit('update:modelValue', address.value);
}

const onUpdated = (model: any) => {
  addressQuery.value = model.address;
  emit('update:modelValue', address.value);
}

const onDelete = () => {
  emit('deleted', props.index);
}
</script>

<style lang="scss">
.address-picker {
  margin-bottom: 10px;

  .el-autocomplete {
    display: block;
  }

  .el-input {
    margin-bottom: 0;
  }
}
</style>