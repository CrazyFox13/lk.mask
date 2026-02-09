<template>
  <v-card>
    <v-toolbar dark color="secondary">
      <v-btn icon dark @click="$emit('close')">
        <v-icon>mdi-close</v-icon>
      </v-btn>
      <v-toolbar-title>Редактирование уведомления</v-toolbar-title>
      <v-spacer></v-spacer>
    </v-toolbar>

    <v-tabs v-model="tab" grow>
      <v-tab>Текста</v-tab>
      <v-tab v-if="pushNotification.id">Получатели</v-tab>
    </v-tabs>

    <v-tabs-items class="mx-3" v-model="tab">
      <v-tab-item>
        <EmailNotificationForm
            @created="onCreated"
            @updated="onUpdated"
            v-on:input="onSaved"
            v-model="pushNotification"
        />
      </v-tab-item>
      <v-tab-item v-if="pushNotification.id">
        <PushNotificationReceivers @users_updated="$emit('users_updated')" :push-notification="pushNotification"/>
      </v-tab-item>
    </v-tabs-items>
  </v-card>
</template>

<script>
import PushNotificationReceivers from "@/components/PushNotification/Editor/PushNotificationReceivers";
import Swal from "sweetalert2-khonik";
import EmailNotificationForm from "@/components/PushNotification/Editor/EmailNotificationForm";

export default {
  name: "EmailNotificationEditDialog",
  components: {EmailNotificationForm, PushNotificationReceivers},
  props: ['value'],
  data() {
    return {
      tab: 0,
      pushNotification: this.value,
    }
  },
  created() {
    this.unsubscribe = this.$store.subscribe((mutation) => {
      if (['detachUserFromNotification', 'attachUserToNotification'].includes(mutation.type)) {
        this.$emit('users_updated');
      }
    });
  },
  methods: {
    onSaved() {
      Swal.fire('Данные сохранены');
    },
    onCreated() {
      this.$emit('created', this.pushNotification);
    },
    onUpdated() {
      this.$emit('updated', this.pushNotification);
    },

  }
}
</script>

<style scoped>

</style>