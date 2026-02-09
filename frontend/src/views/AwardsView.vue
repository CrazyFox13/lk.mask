<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Награды</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить награду</v-btn>
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
        <template v-slot:[`item.icon`]="{item}">
          <v-img :src="item.icon" v-if="item.icon" width="40" height="40"/>
        </template>
        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.actions`]="{item}">
          <v-btn color="warning" icon @click="edit(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn color="error" icon @click="destroy(item)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>
    <v-dialog v-model="editDialog" max-width="500">
      <AwardEditDialog
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
import AwardEditDialog from "@/views/Award/AwardEditDialog";

export default {
  name: "AwardsView",
  components: {AwardEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Иконка", value: "icon", sortable: false},
        {text: "Кол-во компаний", value: "companies_count", sortable: false},
        {text: "Название", value: "name", sortable: false},
        {text: "Описание", value: "description", sortable: false},
        {text: "Дата создания", value: "created_at", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "awards",
      resourceApiRoute: `awards`,
      deleteSwalTitle: `Безвозвратно удалить награду?`,
    }
  },
}
</script>

<style scoped>

</style>