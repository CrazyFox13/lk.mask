<template>
  <v-card>
    <v-card-title>Создание типа техники</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название типа"
          v-model="type.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />
      <!-- <file-uploader
           label="Логотип"
           v-model="type.logo"
           :error="errors.logo"
       />-->
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="create()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
//import FileUploader from "@/components/Common/FileUploader";

export default {
  name: "CreateTypeDialog",
  props: ['group'],
 // components: {FileUploader},
  data() {
    return {
      type: {},
      errors: {},
    }
  },
  methods: {
    create() {
      this.errors = {};
      this.$http.post(`vehicle-groups/${this.group.id}/vehicle-types`, this.type).then(r => {
        this.$emit('created', r.body.vehicleType);
        this.$emit('close');
        this.type={};
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>