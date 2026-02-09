<template>
  <el-image
      :key="photo.id"
      :src="photo.url"
      :alt="photo.description"
      v-bind:style="{'width':`calc(${width}% - 2px)`,'height':`calc(${height}px - 2px)`}"
      :id="`photo-${index}`"
      fit="cover"
      @load="onImgLoaded"
  />
</template>

<script setup lang="ts">

import {computed} from "#imports";

const props = defineProps(['photo', 'index', 'totalCount']);

const height = ref<number>(0);

const width = computed(() => {
  const pLen = props.totalCount;
  if (!pLen) return 0;

  if (pLen % 3 === 0) return 33.33;
  if (pLen % 3 === 1) {
    if (props.index === pLen - 1) return 100;
    return 33.33;
  }

  if (pLen % 3 === 2) {
    if (props.index >= pLen - 2) return 50;
    return 33.33;
  }
  return 100;
});

const onImgLoaded = () => {
  const el = document.getElementById(`photo-${props.index}`);
  if (!el) return;
  height.value = <number>el.clientWidth;
}

</script>

<style scoped>

</style>