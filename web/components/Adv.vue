<template>
  <div>
    <el-tooltip
        v-if="banner && banner.tooltip"
        class="box-item"
        effect="dark"
        :content="banner.tooltip"
        placement="top-start"
    >
      <AdvContent :banner="banner" :key="key" :width="width" :height="height"/>
    </el-tooltip>
    <AdvContent v-else-if="banner" :banner="banner" :key="key" :width="width" :height="height"/>
    <nuxt-img
        v-else
        :src="noAdImg"
        :alt="`default adv/ ${key}`"
        v-bind:style="{'width':`${width}px`,'aspect-ratio':`${width} / ${height}`}"
        class="el-image" loading="lazy"
    />
  </div>
</template>

<script setup lang="ts">
import {computed} from "#imports";
import AdvContent from "~/components/Adv/AdvContent.vue";

const props = defineProps(['banner', 'width', 'height', 'key']);
const adWidth = Number(props.width);
const adHeight = Number(props.height);

const noAdImg = computed(() => {
  switch (true) {
    case adWidth === 262 && adHeight === 400:
      return '/images/default_adv_285x428.png';
    case adWidth === 262 && adHeight === 200:
      return '/images/default_adv_285x214.png';
    case adWidth === 765 && adHeight === 130:
      return '/images/default_adv_777x130.png';
    default:
      return;
  }
});
</script>

<style scoped>
.el-image {
  max-width: 100%;
}
</style>