<template>
  <v-text-field v-model="input" placeholder="Введите число" type="number"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "IntegerForm",
  mixins: [CustomForm],
}
</script>

<style scoped>

</style>