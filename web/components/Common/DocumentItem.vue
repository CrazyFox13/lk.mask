<template>
  <a :href="document.url" target="_blank" class="flex document-item">
    <el-image fit="cover" class="mr-10" v-if="isImage" :src="document.url"/>
    <SvgIcon class="mr-10" v-else :name="iconName"/>
    <div class="file-info">
      <p class="m-0 file-name">{{ fileName }}</p>
      <p class="text-sub-subtitle text-gray m-0">
        {{ documentType }} {{ documentWeight }}
      </p>
    </div>
    <el-button class="delete-btn" circle text @click.prevent="emit('deleted')" v-if="edit">
      <SvgIcon name="close"/>
    </el-button>
  </a>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {computed} from "#imports";
import {formatWeight} from "#imports";

const props = defineProps(['document', 'edit']);
const emit = defineEmits(['deleted']);

const documentType = computed(() => {
  return iconName.value?.toUpperCase();
});

const fileName = computed(() => {
  const parts = props.document.url.split('/');
  return parts.splice(parts.length - 1, 1)[0]
})

const documentWeight = computed(() => {
  return formatWeight(props.document.file_size);
})

const isImage = computed(() => {
  return props.document.mime_type.includes('image');
})

const iconName = computed(() => {
  switch (true) {
    case props.document.mime_type.includes('pdf'):
      return "pdf";
    case props.document.mime_type.includes('doc'):
      return "doc";
    case props.document.mime_type.includes('xls'):
      return "xls";
    case props.document.mime_type.includes('ppt'):
      return "ppt";
    default:
      const parts = props.document.mime_type.split('/');
      return parts[parts.length - 1];
  }
});
</script>

<style lang="scss">
.document-item {
  position: relative;
  padding-bottom: 10px;
  border-bottom: 1px solid #E8EBF1;
  margin-bottom: 10px;
  text-decoration: none;
  color: inherit;

  &:last-of-type {
    border-bottom: none;
  }

  @media (min-width: 992px) {
    border-bottom: none;
    //max-width: 160px;
    width: 25%;
    min-width: 160px;
  }


  .el-image {
    width: 40px;
    height: 40px;
  }
}


.file-info {
  width: calc(100% - 70px);

  .file-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
  }
}

.delete-btn {
  position: absolute;
  top: 0;
  right: 10px;
  width: 16px !important;
  height: 16px !important;

  svg {
    width: 16px;
    height: 16px;
  }
}
</style>