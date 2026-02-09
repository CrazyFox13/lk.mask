<template>
  <v-autocomplete
      label="Компания"
      v-model="input"
      :items="items"
      :loading="isLoading"
      :search-input.sync="search"
      item-value="id"
      item-text="title"
      clearable
  />
</template>

<script>
export default {
  name: "CompanyPicker",
  props: ['value'],
  data() {
    return {
      input: this.value,
      items: [],
      isLoading: false,
      search: "",
    }
  },
  created() {
    this.getItems();
  },
  watch: {
    input() {
      this.$emit("input", this.input)
    },
    search() {
      this.getItems();
    },
  },
  methods: {
    getItems() {
      this.isLoading = true;
      this.$http.get(`companies?search=${this.search || ""}`).then(({body}) => {
        this.items = body.companies;
        this.$nextTick(() => {
          this.isLoading = false;
        })
      })
    }
  }
}
</script>

<style scoped>

</style>