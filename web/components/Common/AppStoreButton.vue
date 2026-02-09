<template>
  <el-link @click="onSearch" :href="iosLink"
           target="_blank">
    <SvgIcon name="app_store_button"/>
  </el-link>
</template>

<script setup>
import SvgIcon from "~/components/SvgIcon";
import {useDeviceStore} from "~/stores/device";

const {iosLink} = useDeviceStore();
const onSearch = () => {
  try {
    ym(65983300, 'reachGoal', 'ios')
  } catch (e) {
    console.log(e);
  }
}
</script>

<style scoped>

</style>