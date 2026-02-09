<template>
  <v-text-field v-model="input"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "TextForm",
  mixins: [CustomForm],
}
</script>

<style scoped>

</style>