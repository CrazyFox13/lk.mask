<template>
  <el-link @click="onClick" :href="androidLink" target="_blank">
    <SvgIcon name="google_play_button"/>
  </el-link>
</template>

<script setup>
import SvgIcon from "~/components/SvgIcon";
import {useDeviceStore} from "~/stores/device";

const {androidLink} = useDeviceStore();

const onClick = () => {
  try {
    ym(65983300, 'reachGoal', 'and')
  } catch (e) {
    console.log(e);
  }
}
</script>

<style scoped lang="scss">

</style>