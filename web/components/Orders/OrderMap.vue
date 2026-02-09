<template>
  <client-only>
    <!-- :cluster-options="clusterOptions" -->
    <YandexMap
        v-if="loaded && coordinates"
        ref="myMap"
        @created="onCreated"
        @boundschange="onBounds"
        v-on:boundschange="onBounds"
        :coordinates="coordinates"
        :controls="[]"
        class="full-screen"
    >
      <YandexClusterer v-if="!updating" :options="clusterOptions()">
        <YandexMarker
            v-for="order in items"
            :key="`marker-${order.id}`"
            :coordinates="[order.start_address.lat,order.start_address.lng]"
            :marker-id="`marker-${order.id}`"
            :options="markerOptions"
            :properties="{}"
        >
          <template #component>
            <OrderBalloon :order="order"/>
          </template>
        </YandexMarker>
      </YandexClusterer>
    </YandexMap>
  </client-only>
</template>

<script setup lang="ts">

import {YandexMap, YandexClusterer, YandexMarker,} from 'vue-yandex-maps'
import {type IGeoCity, useAuthStore} from "~/stores/user";
import {computed, nextTick, ref} from "#imports";
import BalloonIcon from '~/assets/icons/mapBallon.png'
import ClusterIcon from '~/assets/icons/clusterBallon.png'
import OrderBalloon from "~/components/Orders/OrderBalloon.vue";
import type {IAddress} from "~/types/address";

const emit = defineEmits(['onBounds', 'onLoad']);
const props = defineProps(['orders']);
const authStore = useAuthStore();
const myPosition = ref<IGeoCity>();
const loaded = ref(!!authStore.geoCity);
const coordinates = ref<[number, number]>();
const myMap = ref(null);
const updating = ref(false);

if (authStore.geoCity) {
  coordinates.value = [authStore.geoCity.lat, authStore.geoCity.lng];
}

const clusterOptions = () => {
  const MyClusterContentLayout = ymaps.templateLayoutFactory.createClass(
      '<div style="color: #FFFFFF; font-weight: bold;">$[properties.geoObjects.length]</div>'
  );

  return {
    clusterIconContentLayout: MyClusterContentLayout,
    //clusterNumbers: [10],
    clusterIcons: [
      {
        href: ClusterIcon,
        size: [40, 40],
        offset: [-20, -20]
      }
    ],
  }
};

const markerOptions = {
  // Опции.
  // Необходимо указать данный тип макета.
  iconLayout: 'default#image',
  // Своё изображение иконки метки.
  iconImageHref: BalloonIcon,
  // Размеры метки.
  // iconImageSize: [48, 48],
  // Смещение левого верхнего угла иконки относительно
  // её "ножки" (точки привязки).
  // iconImageOffset: [-24, -24],
  // Смещение слоя с содержимым относительно слоя с картинкой.
  // iconContentOffset: [15, 15],
  // Макет содержимого.
  // iconContentLayout: ''
};

const isTooClose = (address1: IAddress, address2: IAddress): boolean => {
  const d1 = Math.abs(address1.lng - address2.lng) < 0.00005;
  const d2 = Math.abs(address1.lat - address2.lat) < 0.00005;

  return d1 && d2;
}

const moveOrder = (order: any, i: number, self: any[]) => {
  const startAddress = order.start_address;
  const theSameIdx = self.findIndex((o: any) => isTooClose(startAddress, o.start_address));
  if (theSameIdx !== i) {
    order.start_address.lat += 0.0001;
    moveOrder(order, i, self);
  }
}

const items = computed(() => {
  const orders = props.orders.filter((x: any) => x.start_address);
  orders.forEach(moveOrder)
  return orders;
})

authStore.$subscribe((mutation, state) => {
  loaded.value = !!state.geoCity;
  if (loaded.value) {
    myPosition.value = state.geoCity;
    coordinates.value = [myPosition.value!.lat, myPosition.value!.lng];
  }
}, {detached: true})

const onCreated = (map: any) => {
  emit('onBounds', {center: map.getCenter(), zoom: map.getZoom()})
  emit('onLoad')
  map.events.add('boundschange', function (e: Event) {
    onBounds(e)
  });
}

const onBounds = (event: any) => {
  updating.value = true;
  emit('onBounds', {center: event.get('newCenter'), zoom: event.get('newZoom')})
  nextTick(() => {
    updating.value = false;
  })
}

</script>

<style>
.yandex-container.full-screen {
  height: calc(100vh - 129px);
  overflow: hidden;
  position: absolute;
  top: 0;
  left: 0;
  right: 100%;
  z-index: 3;
  width: 100%;
}

.yandex-balloon {
  min-height: 192px;
  height: auto;
  width: 330px;
  display: flex;
  align-items: center;
  max-width: 100%;
}
</style>