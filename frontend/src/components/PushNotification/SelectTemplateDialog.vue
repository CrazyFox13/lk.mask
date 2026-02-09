<template>
  <v-card>
    <v-card-title>Выберите шаблон из списка</v-card-title>
    <v-card-text>
      <v-select
          label="Выберите шаблон"
          v-model="template"
          return-object
          :items="templates"
          item-text="subject"
          item-value="id"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="store()">Выбрать</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>

export default {
  name: "SelectTemplateDialog",
  data() {
    return {
      errors: {},
      notification: {},
      template: {},
      templates: [],
    }
  },
  created() {
    this.$http.get(`email-notification-templates`).then(({body}) => {
      this.templates = body.emailNotificationTemplates;
    })
  },
  methods: {
    store() {
      if (!this.template) return;
      this.$http.post(`push-notifications`, {
        subject: this.template.subject,
        text: this.template.text,
        type: "email",
      }).then(r => {
        this.$emit("created", r.body.pushNotification);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
  }
}
</script>

<style scoped>

</style>