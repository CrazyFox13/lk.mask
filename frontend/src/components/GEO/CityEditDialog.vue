<template>
  <v-card>
    <v-card-title>Редактирование города</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="city.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
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
  name: "CityEditDialog",
  props: ['value', 'region_id'],
  data() {
    return {
      city: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.city.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`regions/${this.region_id}/cities`, {
        region_id: this.region_id,
        ...this.city
      }).then(r => {
        this.$emit("created", r.body.city);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`regions/${this.region_id}/cities/${this.city.id}`, this.city).then(r => {
        this.$emit("updated", r.body.city);
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