<template>
  <client-only>
    <el-dialog
        v-if="!isMobile"
        width="430"
        v-model="dialog"
        :before-close="handleClose"
        :show-close="false"
        class="alarm-dialog"
    >
      <template #header="{close}">
        <slot name="header"/>
      </template>
      <slot name="body"/>
    </el-dialog>
    <el-drawer
        v-model="dialog"
        v-else
        direction="btt"
        :before-close="handleClose"
        :show-close="false"
        class="dropdown-drawer"
        :size="drawerHeight?drawerHeight:265"
    >
      <template #header="{close}">
        <slot name="header"/>
      </template>
      <template #default>
        <slot name="body"/>
      </template>
    </el-drawer>
  </client-only>
</template>

<script setup lang="ts">

const emit = defineEmits(['close']);
defineProps(['drawerHeight'])
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {ref} from "#imports";

const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);

const handleClose = () => {
  emit("close");
}
</script>

<style lang="scss">
.alarm-dialog .el-dialog__header {
  @media (min-width: 992px) {
    padding: 20px 40px 10px 40px;
  }
}
</style>