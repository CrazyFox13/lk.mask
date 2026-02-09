<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <v-breadcrumbs :items="breadcrumps" divider="-"/>
      <div class="d-flex align-items-center">
        <div class="text-h6">Создать push уведомление</div>
      </div>
    </v-col>
    <v-col cols="12">
      <v-tabs v-model="tab" grow>
        <v-tab>Настройки</v-tab>
        <v-tab disabled>Материалы</v-tab>
        <v-tab disabled>Получатели</v-tab>
      </v-tabs>

      <v-tabs-items class="mx-3" v-model="tab">
        <v-tab-item>
          <PushNotificationForm
              @created="onCreated"
              v-model="pushNotification"
          />
        </v-tab-item>
      </v-tabs-items>
    </v-col>
  </v-row>
</template>

<script>
import PushNotificationForm from "@/components/PushNotification/Editor/PushNotificationForm";

export default {
  name: "PushNotificationEditDialog",
  components: {PushNotificationForm},
  data() {
    return {
      tab: 0,
      pushNotification: {

      },
    }
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
          text: `Создать уведомление`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    onCreated() {
      this.$router.replace(`/push-notifications/${this.pushNotification.id}/edit`);
    },
  }
}
</script>

<style scoped>

</style>