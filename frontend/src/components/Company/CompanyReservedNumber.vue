<template>
  <v-autocomplete
      label="Забронированный номер"
      hint="Если указано, будет показываться у компании вместо базового номера"
      :items="reservedNumbers"
      v-model="selectedNumber"
      @change="onReservedNumberChanged"
      clearable
      item-value="id"
      item-text="number"
      :loading="isLoading"
      :search-input.sync="search"
  />
</template>

<script>
export default {
  name: "CompanyReservedNumber",
  props: ['company'],
  data() {
    return {
      isLoading: false,
      search: null,
      reservedNumbers: [],
      selectedNumber: this.company.reserved_number ? this.company.reserved_number.id : null,
    }
  },
  created() {
    this.getNumbers();
  },
  watch: {
    search() {
      this.getNumbers();
    }
  },
  methods: {
    getNumbers() {
      if (this.isLoading) return;
      this.isLoading = true;
      this.$http.get(`reserved-numbers?for_company=${this.company.id}&search=${this.search ? this.search : ''}`).then(r => {
        this.reservedNumbers = r.body.reservedNumbers;
        this.isLoading = false;
      })
    },
    onReservedNumberChanged() {
      this.$http.post(`companies/${this.company.id}/set-reserved-number`, {
        reservedNumberId: this.selectedNumber
      });
    }
  }
}
</script>

<style scoped>

</style>