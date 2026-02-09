<template>
  <a
      class="adv"
      :href="banner.endpoint_url"
      target="_blank"
      @click="onClick()"
      v-bind:style="{'width':`${width}px`}">
    <AdvToken class="adv-token" :banner="banner"/>
    <nuxt-img format="webp"
              :src="banner.img_url"
              :alt="`ad/ ${key}`"
              class="el-image"
              loading="lazy"
              v-bind:style="{'width':`${width}px`,'aspect-ratio':`${width} / ${height}`}"
    />
  </a>
</template>

<script setup lang="ts">
import {apiFetch} from "~/composables/apiFetch";
import AdvToken from "~/components/Adv/AdvToken.vue";

const props = defineProps({
  banner: {
    type: Object as any
  },
  width: {
    type: Number,
  },
  height: {
    type: Number,
  },
  key: {
    type: String,
  },
});

const onClick = () => {
  if (!props.banner) return;
  apiFetch(`adv/${props.banner.id}`, {
    method: "POST"
  })
}
</script>

<style scoped lang="scss">
.el-image {
  background: lightgrey;
  border-radius: 10px;
  max-width: 100%;
  object-fit: contain;
}

.adv {
  position: relative;
  display: block;
  max-width: 100%;

  &-token {
    position: absolute;
    right: 4px;
    top: 4px;
    z-index: 2;
  }
}
</style>