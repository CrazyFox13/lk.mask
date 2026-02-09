<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Типы компаний</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить тип</v-btn>
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
        <template v-slot:[`item.is_worker`]="{item}">
          <v-icon v-if="item.is_worker" color="success">mdi-check</v-icon>
        </template>
        <template v-slot:[`item.actions`]="{item}">
          <v-btn icon color="warning" @click="edit(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon color="error" class="ml-2" @click="destroy(item)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>

    <v-dialog v-model="editDialog" max-width="500">
      <CompanyTypeEditDialog
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import CompanyTypeEditDialog from "@/components/CompanyType/CompanyTypeEditDialog";
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";

export default {
  name: "CompanyTypesView",
  components: {CompanyTypeEditDialog},
  mixins:[ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "title", sortable: true},
        {text: "Исполнитель", value: "is_worker", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "companyTypes",
      resourceApiRoute: `company-types`,
      deleteSwalTitle: "Вы действительно хотите удалить тип компаний?"
    }
  },
}
</script>

<style scoped>

</style>