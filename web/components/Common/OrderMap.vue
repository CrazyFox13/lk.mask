<template>
  <YandexMap
      ref="myMap"
      :coordinates="coordinates"
      @created="onMapCreated"
  >
    <YandexMarker
        v-for="(address,i) in addresses"
        :key="`marker-${address.id}`"
        :coordinates="[address.lat,address.lng]"
        :marker-id="`marker-${address.id}`"
        :options="markerOptions()"
        :properties="{ iconContent: markerIndexes[i] }"
    />
  </YandexMap>
</template>

<script setup lang="ts">
import {computed} from "#imports";
import {YandexMap, YandexMarker} from "vue-yandex-maps";
import type {IAddress} from "~/types/address";
import BalloonIcon from "assets/icons/mapBallon.png";
import BalloonIconEmpty from "assets/icons/empty-marker-map.svg";
import {storeToRefs} from "pinia";
import {useOrderStore} from "~/stores/order";
import {useAuthStore} from "~/stores/user";

const {markerIndexes} = storeToRefs(useOrderStore());
const authStore = useAuthStore();

const props = defineProps({
  addresses: {
    type: Array as () => IAddress[],
    required: true,
  },
  vehicleGroupId: {
    type: Number,
  }
})

const coordinates = computed((): [number, number] => {
  const lat = props.addresses.reduce((acc: number, value: any) => acc + value.lat, 0) / props.addresses.length;
  const lng = props.addresses.reduce((acc: number, value: any) => acc + value.lng, 0) / props.addresses.length;
  if (lat && lng) return [lat, lng];
  return [authStore.geoCity ? authStore.geoCity.lat : 55, authStore.geoCity ? authStore.geoCity.lng : 33];
});


const VEHICLE_ROUTE_IDS = [2];
const showRouteOnMap = computed(() => props.vehicleGroupId && VEHICLE_ROUTE_IDS.includes(props.vehicleGroupId))
const onMapCreated = (inst: any) => {
  if (!showRouteOnMap.value) return;

  const points = props.addresses.map((address: IAddress) => {
    return [address.lat, address.lng]
  })

  const multiRoute = new ymaps.multiRouter.MultiRoute({
    referencePoints: points,
  }, {
    wayPointVisible:false,
    pinVisible:false,
    // Внешний вид линии маршрута.
    routeStrokeWidth: 2,
    routeStrokeColor: "rgba(217,60,60,0.18)",
    routeActiveStrokeWidth: 6,
    routeActiveStrokeColor: "#4ad93c",
    // Автоматически устанавливать границы карты так, чтобы маршрут был виден целиком.
    boundsAutoApply: true
  });

  // Добавляем мультимаршрут на карту.
  inst.geoObjects.add(multiRoute);
}


const markerOptions = () => {
  if (props.addresses.length === 1) {
    return {
      iconLayout: 'default#image',
      iconImageHref: BalloonIcon,
    }
  }

  const MyIconContentLayout = ymaps.templateLayoutFactory.createClass(
      '<div style="color: #FFFFFF; font-weight: bold;">$[properties.iconContent]</div>'
  );

  return {
    iconLayout: 'default#imageWithContent',
    iconImageHref: BalloonIconEmpty,
    iconImageSize: [32, 39.4],
    // Смещение левого верхнего угла иконки относительно
    // её "ножки" (точки привязки).
    iconImageOffset: [-16, -39.4],
    // Смещение слоя с содержимым относительно слоя с картинкой.
    iconContentOffset: [11, 10],
    iconContentLayout: MyIconContentLayout
  }
};

</script>

<style scoped lang="scss">

</style>