<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <v-breadcrumbs :items="breadcrumps" divider="-"/>
      <div class="d-flex align-items-center">
        <div class="text-h6">Редактировать push уведомление</div>
      </div>
    </v-col>
    <v-col cols="12" v-if="pushNotification">
      <v-tabs v-model="tab" grow>
        <v-tab>Настройки</v-tab>
        <v-tab>Материалы</v-tab>
        <v-tab>Получатели</v-tab>
      </v-tabs>

      <v-tabs-items class="mx-3" v-model="tab">
        <v-tab-item>
          <PushNotificationForm
              @updated="onUpdated"
              v-on:input="onSaved"
              v-model="pushNotification"
          />
        </v-tab-item>
        <v-tab-item>
          <MaterialForm @updated="onSaved()" :push-notification="pushNotification"/>
        </v-tab-item>
        <v-tab-item>
          <PushNotificationReceivers @users_updated="$emit('users_updated')" :push-notification="pushNotification"/>
        </v-tab-item>
      </v-tabs-items>
    </v-col>
  </v-row>
</template>

<script>
import PushNotificationForm from "@/components/PushNotification/Editor/PushNotificationForm";
import PushNotificationReceivers from "@/components/PushNotification/Editor/PushNotificationReceivers";
import Swal from "sweetalert2-khonik";
import MaterialForm from "@/components/PushNotification/Editor/MaterialForm";

export default {
  name: "PushNotificationEditDialog",
  components: {MaterialForm, PushNotificationReceivers, PushNotificationForm},
  data() {
    return {
      tab: 0,
      pushNotification: undefined,
    }
  },
  created() {
    this.getPushNotification();
    this.unsubscribe = this.$store.subscribe((mutation) => {
      if (['detachUserFromNotification', 'attachUserToNotification'].includes(mutation.type)) {
        this.$emit('users_updated');
      }
    });
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Уведомления',
          disabled: false,
          href: '/admin/push-notifications',
        },
        {
          text: `Редактировать уведомление`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getPushNotification() {
      this.$http.get(`push-notifications/${this.$route.params.id}`).then(({body}) => {
        this.pushNotification = body.pushNotification;
      })
    },
    onSaved() {
      Swal.fire('Данные сохранены');
    },
    onUpdated() {
      this.$emit('updated', this.pushNotification);
    },
  }
}
</script>

<style scoped>

</style>