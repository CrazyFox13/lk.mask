<template>
  <v-select
      label="Рекламное место"
      v-model="adv_place_id"
      :items="advPlaces"
      item-value="id"
      item-text="name"
      :error-messages="error"
      :error-count="1"
      :error="!!error"
      clearable
  />
</template>

<script>
export default {
  name: "AdvPlacePicker",
  props: ['value', 'error'],
  data() {
    return {
      adv_place_id: Number(this.value),
      advPlaces: [],
    }
  },
  created() {
    this.getPlaces();
  },
  computed: {
    selectedPlace() {
      return this.advPlaces.find(i => i.id === this.adv_place_id);
    }
  },
  watch: {
    value(v) {
      this.adv_place_id = Number(v);
    },
    adv_place_id(value) {
      this.$emit("input", value)
    },
    selectedPlace: {
      handler(value) {
        this.$emit("selected", value)
      }, deep: true
    }
  },
  methods: {
    getPlaces() {
      this.$http.get(`adv-places`).then(r => {
        this.advPlaces = r.body.advPlaces;
      })
    },
  }
}
</script>

<style scoped>

</style>