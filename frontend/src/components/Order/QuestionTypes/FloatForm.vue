<template>
  <v-text-field v-model="input" placeholder="Введите число с плавующей точкой"/>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "FloatForm",
  mixins: [CustomForm],
}
</script>

<style scoped>

</style>