<template>
  <div class="menu-list">
    <div
        class="menu-item"
        v-for="(item,key) in items"
        :key="key"
        v-bind:class="{'active text-semibold':isActive(item)}"
    >
      <nuxt-link class="el-link" :to="item.path" @click="emit('go')">
        {{ item.title }}
      </nuxt-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {ref, watch} from "#imports";
type IMaterialPage = {
  title: string
  path: string
};

const props = defineProps(['items', 'currentTab']);
const tab = ref(props.currentTab);
watch(props, () => {
  tab.value = props.currentTab;
}, {deep: true})
const emit = defineEmits(['go']);
const {isMobile} = storeToRefs(useDeviceStore());
const isActive = (item: IMaterialPage) => {
  return !isMobile.value && tab.value?.path === item.path;
}

</script>

<style scoped lang="scss">
.menu-list {
  .menu-item {
    padding: 16px 0;
    border-bottom: 1px solid #E8EBF1;

    /*&:last-of-type {
      border-bottom: none;
    }*/

    &.active {
      color: #2D6679;
    }
  }
}
</style>