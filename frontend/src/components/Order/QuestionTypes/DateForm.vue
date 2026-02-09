<template>
  <v-text-field v-model="input" v-mask="'####-##-##'" placeholder="YYYY-MM-DD"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "DateForm",
  mixins: [CustomForm]
}
</script>

<style scoped>

</style>