<template>
  <v-card>
    <v-card-title>Редактирование типа жалоб</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="reportType.title"
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
  name: "reportTypeTypeEditDialog",
  props: ['value'],
  data() {
    return {
      reportType: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.reportType.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`report-types`, this.reportType).then(r => {
        this.$emit("created", r.body.reportType);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`report-types/${this.reportType.id}`, this.reportType).then(r => {
        this.$emit("updated", r.body.reportType);
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