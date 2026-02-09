<template>
  <v-card>
    <v-card-title>Редактирование региона</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="region.title"
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
  name: "RegionEditDialog",
  props: ['value'],
  data() {
    return {
      region: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.region.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`regions`, this.region).then(r => {
        this.$emit("created", r.body.region);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`regions/${this.region.id}`, this.region).then(r => {
        this.$emit("updated", r.body.region);
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