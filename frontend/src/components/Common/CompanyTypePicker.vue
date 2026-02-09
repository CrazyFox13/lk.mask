<template>
  <v-select
      dense
      label="Тип компании"
      v-model="input"
      hide-details
      :items="items"
      item-value="id"
      item-text="title"
      multiple
  />
</template>

<script>
export default {
  name: "CompanyTypePicker",
  props: ['value'],
  data() {
    return {
      input: this.value,
      items: []
    }
  },
  created() {
    this.getItems();
  },
  watch: {
    input() {
      this.$emit("input", this.input)
    }, deep: true
  },
  methods: {
    getItems() {
      this.$http.get(`company-types`).then(({body}) => {
        this.items = body.companyTypes;
      })
    }
  }
}
</script>

<style scoped>

</style>