<template>
  <v-select v-model="input" :items="options"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "SelectForm",
  mixins: [CustomForm],
  props:['options']
}
</script>

<style scoped>

</style>