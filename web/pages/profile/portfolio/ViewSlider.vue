<template>
  <div>
    <Splide @splice.ready="onMainReady" ref="slider" @splide:move="onMainMoved"
            :options="{ rewind: true,pagination:false, fixedHeight:sliderHeight }" class="photo-slider">
      <SplideSlide v-for="photo in list" :key="photo.id">
        <img class="main-img" :src="photo.url" alt="">
      </SplideSlide>
    </Splide>
    <Splide
        class="photo-slider preview-slider mt-20"
        :options="{ rewind: true,pagination:false, arrows:list.length>paginationLength,perPage:paginationLength }"
    >
      <SplideSlide @click="onPreviewClick(photo)" v-for="photo in list" :key="photo.id">
        <img v-bind:class="{'active':photo.id === activeItem.id}" class="preview-img" :src="photo.url" alt="">
      </SplideSlide>
    </Splide>
  </div>
</template>

<script setup lang="ts">

import {Splide, SplideSlide} from '@splidejs/vue-splide';
import '@splidejs/vue-splide/css';
import {computed, watch} from "#imports";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";

const props = defineProps(['list']);
const activeItem = ref(props.list[0]);
const slider = ref<any>(null);
const {isMobile} = storeToRefs(useDeviceStore())

const paginationLength = computed(() => {
  return isMobile.value ? 6 : 13;
});

const sliderHeight = computed(() => {
  return isMobile.value ? 200 : 420;
})

const onMainReady = () => {
}

const onMainMoved = (ev: any, newIdx: number, oldIdx: number) => {
  activeItem.value = props.list[newIdx];
}

const onPreviewClick = (photo: any) => {
  activeItem.value = photo;
  slider.value!.splide.go(props.list.indexOf(photo))
}

</script>

<style scoped lang="scss">
.main-img {
  max-width: 100%;
  height: 100%;
  display: block;
  margin: auto;
}

.preview-img {
  width: 48px;
  height: 48px;
  object-fit: cover;
  cursor: pointer;
  margin: 2px;

  &.active {
    outline: 2px solid #0167DE;
  }
}
</style>