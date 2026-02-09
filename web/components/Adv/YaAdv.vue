<template>
  <div class="ya-adv" v-bind:style="{'width':`${width}px`,'aspect-ratio':`${width} / ${height}`}">
    <div :id="yaId"></div>
  </div>
</template>
<script setup lang="ts">
const props = defineProps({
  yaId: {
    type: String
  },
  blockId: {
    type: String
  },
  width: {
    type: Number,
  },
  height: {
    type: Number,
  },
});

onMounted(() => {
  (window as any).yaContextCb.push(() => {
    (Ya as any).Context.AdvManager.render({
      "blockId": props.blockId,
      "renderTo": props.yaId
    })
  })
})
</script>
<style scoped>
.ya-adv {
  max-width: 100%
}
</style>