<template>
  <v-radio-group v-model="input">
    <v-radio
        v-for="(opt,n) in options"
        :key="n"
        :label="opt"
        :value="opt"
    ></v-radio>
  </v-radio-group>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "RadioForm",
  mixins: [CustomForm],
  props: ['options']
}
</script>

<style scoped>

</style>