<template>
  <v-autocomplete
      label="Рекламодатель"
      v-model="advertiser_id"
      :items="advertisers"
      :loading="isLoadingAdvertisers"
      :search-input.sync="searchAdvertisers"
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
  name: "AdvertiserPicker",
  props: ['value', 'error'],
  data() {
    return {
      advertiser_id: Number(this.value),
      advertisers: [],
      isLoadingAdvertisers: false,
      searchAdvertisers: '',
    }
  },
  watch: {
    value(v) {
      this.advertiser_id = Number(v);
    },
    searchAdvertisers() {
      this.getAdvertisers();
    },
    advertiser_id(value) {
      this.$emit("input", value)
    }
  },
  created() {
    this.getAdvertisers();
  },
  methods: {
    getAdvertisers() {
      if (this.isLoadingAdvertisers) return;
      this.isLoadingAdvertisers = true;
      this.$http.get(`advertisers?search=${this.searchAdvertisers ? this.searchAdvertisers : ''}&field=${this.advertiser_id ? this.advertiser_id : ''}`).then(r => {
        this.advertisers = r.body.advertisers;
        this.isLoadingAdvertisers = false;
      })
    },
  }
}
</script>

<style scoped>

</style>