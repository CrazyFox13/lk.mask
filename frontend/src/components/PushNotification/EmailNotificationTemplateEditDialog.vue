<template>
  <v-card>
    <v-card-title>Редактирование шаблона</v-card-title>
    <v-card-text>
      <v-text-field
          label="Тема"
          v-model="template.subject"
          :error-messages="errors.subject"
          :error-count="1"
          :error="!!errors.subject"
      />
      <v-textarea
          label="Текст письма"
          v-model="template.text"
          :error-messages="errors.text"
          :error-count="1"
          :error="!!errors.text"
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
  name: "EmailNotificationTemplateEditDialog",
  props: ['value'],
  data() {
    return {
      template: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.template.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`email-notification-templates`, this.template).then(r => {
        this.$emit("created", r.body.emailNotificationTemplate);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`email-notification-templates/${this.template.id}`, this.template).then(r => {
        this.$emit("updated", r.body.emailNotificationTemplate);
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