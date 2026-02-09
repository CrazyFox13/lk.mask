<template>
  <v-card flat>
    <v-card-text>
      <v-text-field
          label="Заголовок"
          v-model="pushNotification.subject"
          :error-messages="errors.subject"
          :error-count="1"
          :error="!!errors.subject"
      />
      <v-textarea
          label="Текст"
          v-model="pushNotification.text"
          :error-messages="errors.text"
          :error-count="1"
          :error="!!errors.text"
      />
      <!--<v-select
          label="Действие"
          v-model="pushNotification.action"
          :items="actionList"
          item-value="value"
          item-text="text"
          :error-messages="errors.action"
          :error-count="1"
          :error="!!errors.subject"
          clearable
      />-->
    </v-card-text>
    <v-card-actions>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export const PushNotificationActionList = [
  {
    value: 'orders_list',
    text: "Список заявок"
  },
  {
    value: 'orders_map',
    text: "Карта заявок"
  },
  {
    value: 'companies_list',
    text: "Список компаний"
  },
];

export default {
  name: "EmailNotificationForm",
  props: ['value'],
  data() {
    return {
      pushNotification: this.value,
      errors: {},
      actionList: PushNotificationActionList
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.pushNotification.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`push-notifications`, {...this.pushNotification, type: "email"}).then(r => {
        this.$emit("input", r.body.pushNotification);
        this.$emit("created", r.body.pushNotification);
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`push-notifications/${this.pushNotification.id}`, this.pushNotification).then(r => {
        this.$emit("input", r.body.pushNotification);
        this.$emit("updated", r.body.pushNotification);
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>