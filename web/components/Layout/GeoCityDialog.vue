<template>
  <el-dialog
      width="480"
      v-model="dialog"
      :fullscreen="isFullScreenDialog"
      :before-close="handleClose"
      :show-close="false"
  >
    <template #header="{close}">
      <img :src="Logo" alt="Логотип" class="hidden-md-and-up m-logo"/>
      <el-button class="close-btn text-black" circle text @click="close">
        <SvgIcon name="close"/>
      </el-button>
      <div class="text-black text-h3 hidden-sm-and-down">Выбор региона</div>
    </template>
    <div>
      <div class="text-black text-h4 m-title hidden-md-and-up">Выбор региона</div>
      <SearchInput placeholder="Введите название города" v-model="search"/>
      <ul>
        <li v-for="(item,k) in list" :key="k">
          <div class="text-bold m-title" v-if="item.type==='region'">{{ item.title }}</div>
          <el-button v-else link @click="selectCity(item)">{{ item.title }}</el-button>
        </li>
      </ul>
    </div>
  </el-dialog>
</template>

<script setup lang="ts">
import SearchInput from "../Common/Forms/SearchInput.vue";
import {apiFetch} from "~/composables/apiFetch";
import {computed, onMounted, watch} from "#imports";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import SvgIcon from "~/components/SvgIcon.vue";
import Logo from "~/assets/images/logo-mask-group.png.jpg";
import {useAuthStore} from "~/stores/user";
import {type IGeoCity, type IGeoRegion,} from "~/stores/user";
//const {debounce} = require("lodash")
import debounce from 'lodash.debounce';

interface IGeoListItem {
  type: 'region' | 'city'
  geo_region_id: number
  geo_city_id: number | null
  title: string
}

const props = defineProps(['modelValue']);
const emits = defineEmits(['update:modelValue']);

const dialog = ref(false);
const {isMobile} = storeToRefs(useDeviceStore());
const {confirmCity, setGeoCity} = useAuthStore();
const isFullScreenDialog = ref(isMobile.value);
const FEDERAL_CITIES_ID = [77, 78];
const search = ref('');
const regions = ref([]);

watch(props, () => {
  isFullScreenDialog.value = isMobile.value;
  dialog.value = props.modelValue;
})

watch(search, () => {
  fetchCities()
});

const handleClose = () => {
  emits('update:modelValue', false);
}

const fetchCities = debounce(async () => {
  const data = await apiFetch(`geo-regions?city=${search.value}`)
  regions.value = data.regions;
}, 500);

fetchCities();

const list = computed((): IGeoListItem[] => {
  return regions.value.reduce((acc: any[], item: IGeoRegion) => {
    acc = acc.concat([
      ...!FEDERAL_CITIES_ID.includes(item.id) ? [{
        type: 'region',
        geo_region_id: item.id,
        geo_city_id: null,
        title: item.name_with_type,
      }] : [],
      ...item.cities ? item.cities.map((city: IGeoCity) => {
        return {
          type: 'city',
          geo_region_id: city.geo_region_id,
          geo_city_id: city.id,
          title: city.name,
        }
      }) : []
    ]);
    return acc;
  }, []).filter(i => i.title.toLowerCase().includes(search.value.toLowerCase()));
});

const selectCity = (item: IGeoListItem) => {
  const cities = <IGeoCity[]>regions.value
      .filter((region: IGeoRegion) => region.cities && region.cities.length > 0)
      .map((region: IGeoRegion) => region.cities)
      .flat();

  const city = cities.find((city: IGeoCity) => city.geo_region_id === item.geo_region_id && city.id === item.geo_city_id);
  if (!city) return;
  setGeoCity(city);
  confirmCity();
  handleClose();
}
</script>

<style scoped lang="scss">
ul {
  max-height: 450px;
  overflow-y: scroll;
  padding-left: 0;
  list-style: none;
  margin-top: 18px;

  li {
    margin-bottom: 18px;
  }
}

.m-title {
  margin-top: 4px;
  margin-bottom: 18px;
}

.m-logo {
  width: 120px;
  height: auto;
  display: block;
}
</style>