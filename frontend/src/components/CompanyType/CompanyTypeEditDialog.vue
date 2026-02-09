<template>
  <v-card>
    <v-card-title>Редактирование типа компании</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="companyType.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />
      <v-switch
          label="Исполнитель"
          v-model="companyType.is_worker"
          :error-messages="errors.is_worker"
          :error-count="1"
          :error="!!errors.is_worker"
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
  name: "CompanyTypeEditDialog",
  props: ['value'],
  data() {
    return {
      companyType: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.companyType.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`company-types`, this.companyType).then(r => {
        this.$emit("created", r.body.companyType);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`company-types/${this.companyType.id}`, this.companyType).then(r => {
        this.$emit("updated", r.body.companyType);
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