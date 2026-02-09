<template>
  <div class="d-flex">
    <v-text-field v-model="dFrom" v-mask="'####-##-##'" placeholder="YYYY-MM-DD"/>
    <v-text-field class="ml-3" v-model="dTo" v-mask="'####-##-##'" placeholder="YYYY-MM-DD"/>
  </div>
</template>

<script>
import CustomForm from "@/mixins/CustomForm";

export default {
  name: "DateRangeForm",
  mixins: [CustomForm],
  data() {
    return {
      dFrom: this.value[0],
      dTo: this.value[1],
    }
  },
  watch: {
    dFrom: {
      handler() {
        this.input = [this.dFrom, this.dTo]
      }, deep: true
    },
    dTo: {
      handler() {
        this.input = [this.dFrom, this.dTo]
      }, deep: true
    },

  }
}
</script>

<style scoped>

</style>