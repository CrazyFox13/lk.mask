<template>
  <el-dialog
      width="700"
      v-model="dialog"
      :fullscreen="isFullScreenDialog"
      :before-close="handleClose"
      :show-close="false"
  >
    <template #header="{close}">
      <div class="dialog-head">
        <el-button class="close-btn text-black hidden-md-and-up" circle text @click="close()">
          <SvgIcon name="close"/>
        </el-button>
        <div class="text-black modal-window-title m-0">Выбрать на карте</div>
        <el-button link size="small" class="text-gray reset-btn hidden-md-and-up" @click="reset()">Отменить</el-button>
        <el-button class="close-btn text-black hidden-sm-and-down" circle text @click="close()">
          <SvgIcon name="close"/>
        </el-button>
      </div>
    </template>
    <div>
      <el-autocomplete
          v-model="addressQuery"
          placeholder="Начните вводить адрес"
          @select="handleSelect"
          :fetch-suggestions="querySearchAsync"
      >
      </el-autocomplete>
      <YandexMap
          class="mt-20"
          ref="myDialogMap"
          :coordinates="coordinates"
          @click="onClick"
      >
        <YandexMarker
            v-if="selectedAddress"
            key="selectedAddress"
            :coordinates="[selectedAddress.lat,selectedAddress.lng]"
            marker-id="selectedAddress"
            :options="{
              iconLayout: 'default#image',
              iconImageHref: BalloonIcon,
              }"
        />
      </YandexMap>
      <div class="flex mt-20">
        <el-button class="btn-submit" type="primary" @click="submit()">Применить</el-button>
        <el-button class="btn-submit hidden-sm-and-down" type="primary" plain @click="reset()">Отменить</el-button>
      </div>
    </div>
  </el-dialog>
</template>

<script setup lang="ts">
import {YandexMap, YandexMarker} from "vue-yandex-maps";
import BalloonIcon from "~/assets/icons/mapBallon.png";
import SvgIcon from "../../SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, ref} from "#imports";
import {useAuthStore} from "~/stores/user";

const dialog = ref(true);
const props = defineProps(['query', 'modelValue']);
const emit = defineEmits(['close', 'update:modelValue']);
const {isMobile} = storeToRefs(useDeviceStore());
const isFullScreenDialog = ref(isMobile.value);
const addressQuery = ref(props.query);
const authStore = useAuthStore();
const coordinates = ref([authStore.geoCity ? authStore.geoCity.lat : 55, authStore.geoCity ? authStore.geoCity.lng : 33]);
const selectedAddress = ref(props.modelValue)
const handleClose = () => {
  emit("close");
}

const querySearchAsync = async (queryString: string, cb: (arg: any) => void) => {
  if (queryString.length === 0) return;
  const {result} = await apiFetch(`address-from-string?query=${queryString}`);
  cb(result);
}

const handleSelect = (item: any) => {
  selectedAddress.value = {
    address: item.value,
    lat: Number(item.data.geo_lat),
    lng: Number(item.data.geo_lon),
    geo_city_id: undefined,
    geo_region_id: undefined,
    fias_id: item.data.city_fias_id,
    region_fias_id: item.data.region_fias_id
  };
}

const onClick = async (e: any) => {
  const coords = e.get('coords');
  const {result} = await apiFetch(`address-from-geo?lat=${coords[0]}&lng=${coords[1]}`);

  if (result.length > 0) {
    const {data, value} = result[0];
    addressQuery.value = value;
    selectedAddress.value = {
      address: value,
      lat: Number(data.geo_lat),
      lng: Number(data.geo_lon),
      geo_city_id: undefined,
      geo_region_id: undefined,
      fias_id: data.city_fias_id,
      region_fias_id: data.region_fias_id
    }
  }
};

const reset = () => {
  emit("close");
}

const submit = () => {
  emit('update:modelValue', selectedAddress.value);
  emit("close");
}
</script>

<style scoped lang="scss">
.dialog-head {
  text-align: center;

  @media (min-width: 992px) {
    text-align: left;
  }

  .close-btn {
    left: 23px;

    @media (min-width: 992px) {
      left: auto;
    }
  }

  .reset-btn {
    position: absolute;
    right: 5px;
    top: 16px;
  }
}
</style>