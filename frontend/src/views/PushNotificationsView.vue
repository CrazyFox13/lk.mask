<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Push - уведомления</div>
        <v-spacer/>
        <v-btn small @click="$router.push(`/push-notifications/create`)" color="primary">Создать</v-btn>
      </div>
    </v-col>
    <v-col cols="12">
      <v-data-table
          :headers="headers"
          :items="items"
          :options.sync="options"
          :server-items-length="totalItems"
          :loading="loading"
          class="elevation-1 mt-3"
      >
        <template v-slot:[`item.id`]="{item}">
          <v-btn text :to="`/push-notifications/${item.id}`">
            {{ item.id }}
          </v-btn>
        </template>

        <template v-slot:[`item.progress`]="{item}">
          <v-progress-linear :value="item.progress"/>
        </template>

        <template v-slot:[`item.status`]="{item}">
          {{ pushStatusLabelText(item.status) }}
        </template>
        <template v-slot:[`item.schedules`]="{item}">
          <div v-if="item.schedules.length===0">
            Сразу
          </div>
          <div v-else>
            {{ item.schedules.map(i => moment(i.time).format("DD.MM.YYYY в HH:mm")).join(', ') }}
          </div>
        </template>

        <template v-slot:[`item.action`]="{item}">
          {{ getActionLabel(item.action) }}
        </template>
        <template v-slot:[`item.actions`]="{item}">
          <NotificationActions @created="onCreated" v-model="item.status" :notification="item"/>
          <v-btn icon color="warning" :to="`/push-notifications/${item.id}/edit`" :disabled="!item.editable">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon color="error" class="ml-2" @click="destroy(item)" :disabled="!item.editable">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>
  </v-row>
</template>

<script>
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import ResourceStatuses from "@/mixins/ResourceStatuses";
import {PushNotificationActionList} from "@/components/PushNotification/Editor/PushNotificationForm";
import NotificationActions from "@/components/PushNotification/NotificationActions";

export default {
  name: "PushNotificationsView",
  components: {NotificationActions},
  mixins: [ResourceComponentHelper, ResourceStatuses],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "subject", sortable: true},
        {text: "Действие", value: "action", sortable: true},
        {text: "Кол-во получателей", value: "users_count", sortable: true},
        {text: "Прогресс", value: "progress", sortable: true},
        {text: "Статус", value: "status", sortable: true},
        {text: "Отправка", value: "schedules", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "pushNotifications",
      resourceApiRoute: `push-notifications`,
      resourceApiParams: 'type=push',
      deleteSwalTitle: "Вы действительно хотите удалить уведомление?",
    }
  },
  methods: {
    onCreated(resource) {
      this.$set(this, 'items', [
        resource,
        ...this.items,
      ])
    },
    onUsersUpdated() {
      this.getItems()
    },
    getActionLabel(action) {
      return PushNotificationActionList.find(i => i.value === action)?.text;
    },
  }
}
</script>

<style scoped>

</style>