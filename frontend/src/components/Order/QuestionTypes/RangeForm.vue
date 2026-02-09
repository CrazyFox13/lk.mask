<template>
  <v-range-slider v-model="input"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "RangeForm",
  mixins: [CustomForm],
}
</script>

<style scoped>

</style>