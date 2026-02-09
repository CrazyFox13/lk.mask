<template>
  <v-autocomplete
      label="Баннер"
      v-model="adv_banner_id"
      :items="banners"
      :loading="isLoading"
      :search-input.sync="search"
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
  name: "AdvBannerPicker",
  props: ['value', 'error', 'place_id', 'advertiser_id'],
  data() {
    return {
      adv_banner_id: Number(this.value),
      banners: [],
      isLoading: false,
      search: '',
    }
  },
  watch: {
    value(v) {
      this.adv_banner_id = Number(v);
    },
    searchAdvertisers() {
      this.getBanners();
    },
    adv_banner_id(value) {
      this.$emit("input", value)
    },
    place_id() {
      this.getBanners();
    },
    advertiser_id() {
      this.getBanners();
    }
  },
  created() {
    this.getBanners();
  },
  methods: {
    getBanners() {
      if (this.isLoading) return;
      this.isLoading = true;
      this.$http.get(`adv-banners?search=${this.search ? this.search : ''}&field=${this.advertiser_id ? this.advertiser_id : ''}&advertiser_id=${this.advertiser_id ? this.advertiser_id : ''}&adv_place_id=${this.place_id ? this.place_id : ''}`).then(r => {
        this.banners = r.body.advBanners;
        this.isLoading = false;
      })
    },
  }
}
</script>

<style scoped>

</style>