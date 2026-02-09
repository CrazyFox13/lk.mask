<template>
  <div>
    <v-progress-circular indeterminate color="primary" v-if="!notification"/>
    <div v-else>
      <v-breadcrumbs :items="breadcrumps" divider="-"/>
      <h4 class="text-h4 mb-3">{{ notification.subject }}</h4>
      <div class="d-flex">
        <v-chip class="mr-3" :color="pushStatusLabelColor(notification.status)">
          {{ pushStatusLabelText(notification.status) }}
        </v-chip>
        <v-chip class="mr-3">
          <v-icon>mdi-account</v-icon>
          {{ notification.users_count }}
        </v-chip>
        <NotificationActions v-model="notification.status" :notification="notification"/>
      </div>

      <v-row>
        <v-col cols="12" sm="8" md="6">
          <p class="mt-3">{{ notification.text }}</p>
        </v-col>
        <v-col cols="12" v-if="notification.schedules.length===0">
          <NotificationUsers :notification="notification"/>
        </v-col>
        <v-col v-else cols="12">
          <v-tabs
              v-model="tab"
              background-color="transparent"
              color="basil"
              grow
          >
            <v-tab
                v-for="schedule in notification.schedules"
                :key="schedule.id"
            >
              <div class="flex flex-column">
                <div>
                  {{ moment(schedule.time).format("DD.MM.YYYY в HH:mm") }}
                </div>
                <v-chip x-small v-if="schedule.status==='waiting'">Ожидание</v-chip>
                <v-chip x-small color="primary" v-if="schedule.status==='running'">Выполняется</v-chip>
                <v-chip x-small color="success" v-if="schedule.status==='completed'">Выполнено</v-chip>
              </div>
            </v-tab>
          </v-tabs>

          <v-tabs-items v-model="tab">
            <v-tab-item
                v-for="schedule in notification.schedules"
                :key="schedule.id"
            >
              <NotificationUsers :notification="notification" :schedule_id="schedule.id"/>
            </v-tab-item>
          </v-tabs-items>
        </v-col>
      </v-row>
    </div>
  </div>
</template>

<script>
import ResourceStatuses from "@/mixins/ResourceStatuses";
import NotificationUsers from "@/components/PushNotification/NotificationUsers";
import moment from "moment";
import NotificationActions from "@/components/PushNotification/NotificationActions";

export default {
  name: "PushNotificationItem",
  components: {NotificationActions, NotificationUsers},
  mixins: [ResourceStatuses],
  data() {
    return {
      pushNotificationId: Number(this.$route.params.id),
      notification: undefined,
      tab: 0,
      moment: moment,
    }
  },
  created() {
    this.getNotification();
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
          text: `${this.notification?.subject}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getNotification() {
      this.$http.get(`push-notifications/${this.pushNotificationId}`).then(r => {
        this.notification = r.body.pushNotification;
      });
    },
  }
}
</script>

<style scoped>

</style>