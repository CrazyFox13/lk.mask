<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Номера компаний</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить номер</v-btn>
      </div>
    </v-col>
    <v-col cols="12">
      <v-card>
        <v-card-title>Фильтр</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                  label="Поиск"
                  hide-details
                  v-model="query.search"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                  label="Статус"
                  v-model="query.status"
                  hide-details
                  :items="[
                      {key:'',value:'Все'},
                      {key:'busy',value:'Занятые'},
                      {key:'free',value:'Свободные'},

                  ]"
                  item-value="key"
                  item-text="value"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="replaceRoute">Найти</v-btn>
        </v-card-actions>
      </v-card>
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

        <template v-slot:[`item.company`]="{item}">
          <v-btn text x-small v-if="item.company" :to="`/companies/${item.company.id}`">{{ item.company.title }}</v-btn>
          <i v-else>Свободно</i>
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
      <ReservedNumberEditDialog
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
import ReservedNumberEditDialog from "@/components/ReservedNumber/ReservedNumberEditDialog";

export default {
  name: "ReservedNumberView",
  components: {ReservedNumberEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Номер", value: "number", sortable: true},
        {text: "Компания", value: "company", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "reservedNumbers",
      resourceApiRoute: `reserved-numbers`,
      deleteSwalTitle: "Вы действительно хотите удалить номер?"
    }
  },
}
</script>

<style scoped>

</style>