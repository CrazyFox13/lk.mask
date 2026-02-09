<template>
  <v-text-field v-model="input" placeholder="https://..."/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "LinkForm",
mixins: [CustomForm],
}
</script>

<style scoped>

</style>