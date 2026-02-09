<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Email - уведомления</div>
        <v-spacer/>
        <v-btn small @click="selectTemplate=true" color="secondary" class="mr-2">Из шаблона</v-btn>
        <v-btn small @click="create()" color="primary">Создать</v-btn>
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
        <template v-slot:[`item.action`]="{item}">
          {{ getActionLabel(item.action) }}
        </template>
        <template v-slot:[`item.actions`]="{item}">
          <v-menu offset-y v-if="item.progress>0 && item.progress<100">
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                  color="primary"
                  icon
                  v-bind="attrs"
                  v-on="on"
              >
                <v-icon>mdi-content-copy</v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item>
                <v-list-item-title @click="copy(item)">Скопировать полностью</v-list-item-title>
                <v-list-item-title @click="copy(item,true)">Только с неотправленными</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
          <NotificationActions @created="onCreated" v-model="item.status" :notification="item"/>
          <v-btn icon color="warning" @click="edit(item)" :disabled="!item.editable">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon color="error" class="ml-2" @click="destroy(item)" :disabled="!item.editable">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>
    <v-dialog v-model="editDialog"
              fullscreen
              hide-overlay
              persistent
              transition="dialog-bottom-transition">
      <EmailNotificationEditDialog
          v-if="editDialog"
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
          @users_updated="onUsersUpdated"
      />
    </v-dialog>

    <v-dialog max-width="400" v-model="selectTemplate">
      <SelectTemplateDialog
          v-if="selectTemplate"
          @close="selectTemplate=false"
          @created="onTemplated"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import ResourceStatuses from "@/mixins/ResourceStatuses";
import {PushNotificationActionList} from "@/components/PushNotification/Editor/PushNotificationForm";
import EmailNotificationEditDialog from "@/components/PushNotification/EmailNotificationEditDialog";
import SelectTemplateDialog from "@/components/PushNotification/SelectTemplateDialog";
import NotificationActions from "@/components/PushNotification/NotificationActions";

export default {
  name: "EmailNotificationsView",
  components: {NotificationActions, SelectTemplateDialog, EmailNotificationEditDialog},
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
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "pushNotifications",
      resourceApiRoute: `push-notifications`,
      resourceApiParams: 'type=email',
      deleteSwalTitle: "Вы действительно хотите удалить уведомление?",

      selectTemplate: false,
    }
  },
  methods: {
    onCreated(resource) {
      this.$set(this, 'items', [
        resource,
        ...this.items,
      ])
    },
    onTemplated(resource) {
      this.onCreated(resource)
      this.$nextTick(() => {
        this.edit(resource);
      })
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