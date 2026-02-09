<template>
  <v-select
      label="Категория техники"
      v-model="category_id"
      :items="categories"
      item-value="id"
      item-text="title"
      :error-messages="error"
      :error-count="1"
      :error="!!error"
      clearable
  />
</template>

<script>
export default {
  name: "VehicleCategoryPicker",
  props: ['value', 'error'],
  data() {
    return {
      category_id: Number(this.value),
      categories: [],
    }
  },
  created() {
    this.getCategories();
  },
  computed: {
    selectedCategory() {
      return this.categories.find(i => i.id === this.category_id);
    }
  },
  watch: {
    value(v) {
      this.category_id = Number(v);
    },
    category_id(value) {
      this.$emit("input", value)
    },
    selectedCategory: {
      handler(value) {
        this.$emit("selected", value)
      }, deep: true
    }
  },
  methods: {
    getCategories() {
      this.$http.get(`vehicle-categories`).then(r => {
        this.categories = r.body.vehicleCategories;
      })
    },
  }
}
</script>

<style scoped>

</style>