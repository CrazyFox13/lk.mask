<template>
  <v-card>
    <v-card-title>Создание группы техники</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название группы"
          v-model="group.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />
      <file-uploader
          label="Логотип"
          v-model="group.logo"
          :error="errors.logo"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="create()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import FileUploader from "@/components/Common/FileUploader";
export default {
  name: "CreateDialog",
  components: {FileUploader},
  data() {
    return {
      group: {},
      errors: {},
    }
  },
  methods: {
    create() {
      this.errors = {};
      this.$http.post(`vehicle-groups`, this.group).then(r => {
        this.$emit('created', r.body.vehicleGroup);
        this.$emit('close');
        this.group={};
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>