<template>
  <el-row>
    <el-col :span="6"  v-for="group in groups" :key="group.id">
      <VehicleGroup :group="group"/>
    </el-col>
  </el-row>
</template>

<script setup>
import VehicleGroup from "~/components/Index/OrdersSection/VehicleGroup";

const {groups} = defineProps(['groups'])
</script>

<style scoped>

</style>