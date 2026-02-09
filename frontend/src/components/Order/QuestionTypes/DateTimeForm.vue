<template>
  <v-text-field v-bind="$attrs" v-model="input" v-mask="'####-##-## ##:##'" placeholder="YYYY-MM-DD HH:mm"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "DateTimeForm",
  mixins: [CustomForm]
}
</script>

<style scoped>

</style>