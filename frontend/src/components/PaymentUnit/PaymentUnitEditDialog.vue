<template>
  <v-card>
    <v-card-title>Редактирование типа оплаты</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="paymentUnit.name"
          :error-messages="errors.name"
          :error-count="1"
          :error="!!errors.name"
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
  name: "PaymentUnitEditDialog",
  props: ['value'],
  data() {
    return {
      paymentUnit: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.paymentUnit.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`payment-units`, this.paymentUnit).then(r => {
        this.$emit("created", r.body.paymentUnit);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`payment-units/${this.paymentUnit.id}`, this.paymentUnit).then(r => {
        this.$emit("updated", r.body.paymentUnit);
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