<template>
  <span class="slot-container">
    <span class="slot" v-bind:style="{'margin':margin}"><slot name="content"/></span>
    <slot name="badge"/>
    <span style="display: inline-flex" v-bind:style="{'width':width,'height':height}" v-html="icon"/>
    </span>
</template>

<script setup lang="ts">
import {useSlots} from 'vue'
import {onMounted} from "#imports";

const slots = useSlots();
const props = defineProps<{
  name?: string,
  margin?: string
  width?: string
  height?: string
}>()

// Auto-load icons
const icons = Object.fromEntries(
    Object.entries(import.meta.glob('~/assets/icons/*.svg', {as: 'raw'})).map(
        ([key, value]) => {
          const filename = key.split('/').pop()!.split('.').shift()
          return [filename, value]
        },
    ),
)

// Lazily load the icon
const defaultHtml = props.name && (await icons?.[props.name]?.());
const icon = ref(defaultHtml)

onMounted(() => {
  const div = document.createElement('div');
  div.innerHTML = icon.value;

  const svg = div.querySelector('svg');
  if (props.width) svg!.setAttribute('width', props.width)
  if (props.height) svg!.setAttribute('height', props.height);
  icon.value = svg?.outerHTML ;
})

// Change this to div.childNodes to support multiple top-level nodes.

</script>

<style lang="scss">
.slot-container {
  position: relative;
  display: inline-flex;

  .slot {
    position: absolute;
    transform: translate(-50%, -50%);
    left: 50%;
    top: 50%;
    font-style: normal
  }
}
</style>