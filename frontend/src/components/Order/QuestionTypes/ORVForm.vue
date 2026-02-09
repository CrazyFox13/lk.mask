<template>
  <v-checkbox v-model="input"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "ORVForm",
  mixins: [CustomForm],
}
</script>

<style scoped>

</style>