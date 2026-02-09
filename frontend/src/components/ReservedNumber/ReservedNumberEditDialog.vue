<template>
  <v-card>
    <v-card-title>Редактирование номера</v-card-title>
    <v-card-text>
      <v-text-field
          label="Номер"
          v-model="reservedNumber.number"
          :error-messages="errors.number"
          :error-count="1"
          :error="!!errors.number"
          :counter="6"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>

export default {
  name: "ReservedNumberEditDialog",
  props: ['value'],
  data() {
    return {
      reservedNumber: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.reservedNumber.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`reserved-numbers`, this.reservedNumber).then(r => {
        this.$emit("created", r.body.reservedNumber);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`reserved-numbers/${this.reservedNumber.id}`, this.reservedNumber).then(r => {
        this.$emit("updated", r.body.reservedNumber);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>