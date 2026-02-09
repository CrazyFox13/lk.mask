<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Шаблоны e-mail уведомлений</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Создать шаблон</v-btn>
      </div>
    </v-col>
    <v-col cols="12">
      <v-data-table
          :headers="headers"
          :items="items"
          :options="options"
          :loading="loading"
          class="elevation-1 mt-3"
          disable-pagination
      >
        <template v-slot:[`item.actions`]="{item}">
          <v-btn icon color="warning" @click="edit(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon color="error" class="ml-2" @click="destroy(item,false)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>

    <v-dialog v-model="editDialog" max-width="500">
      <EmailNotificationTemplateEditDialog
          v-if="editDialog"
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import EmailNotificationTemplateEditDialog from "@/components/PushNotification/EmailNotificationTemplateEditDialog";

export default {
  name: "EmailNotificationTemplatesView",
  components: {EmailNotificationTemplateEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Тема письма", value: "subject", sortable: false},
        {text: "Текст письма", value: "text", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "emailNotificationTemplates",
      resourceApiRoute: `email-notification-templates`,
      deleteSwalTitle: "Вы действительно хотите удалить шаблон?"
    }
  },
}
</script>

<style scoped>

</style>