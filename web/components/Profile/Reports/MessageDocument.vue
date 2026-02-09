<template>
  <a :href="url" target="_blank" class="flex document-message">
    <SvgIcon class="mr-10" :name="iconName"/>
    <div class="file-info">
      <p class="m-0 file-name">{{ fileName }}</p>
      <p class="text-sub-subtitle text-gray m-0">
        {{ documentType }}
      </p>
    </div>
  </a>
</template>

<script setup lang="ts">

import SvgIcon from "~/components/SvgIcon.vue";
import {computed} from "#imports";
import {formatWeight} from "#imports";

const props = defineProps(['url']);
const emit = defineEmits(['deleted']);

const documentType = computed(() => {
  return iconName.value?.toUpperCase();
});

const fileName = computed(() => {
  const parts = props.url.split('/');
  return parts.splice(parts.length - 1, 1)[0]
})

const iconName = computed(() => {
  switch (true) {
    case props.url.includes('pdf'):
      return "pdf";
    case props.url.includes('doc'):
      return "doc";
    case props.url.includes('xls'):
      return "xls";
    case props.url.includes('ppt'):
      return "ppt";
    default:
      const parts = props.url.split('/');
      return parts[parts.length - 1];
  }
});
</script>

<style lang="scss">
.document-message {
  text-decoration: none;
  color: inherit;
  .file-info {
    width: calc(100% - 70px);
    text-align: left !important;

    .file-name {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      width: 100%;
    }
  }
}
</style>