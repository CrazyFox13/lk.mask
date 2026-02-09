<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Регионы</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить регион</v-btn>
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
        <template v-slot:[`item.title`]="{item}">
          <router-link :to="`/regions/${item.id}/cities`">{{ item.title }}</router-link>
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
      <RegionEditDialog
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import RegionEditDialog from "@/components/GEO/RegionEditDialog";
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";

export default {
  name: "RegionsView",
  components: {RegionEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "name", sortable: true},
        {text: "Кол-во городов", value: "cities_count", sortable: true},
        {text: "Кол-во заказов", value: "order_addresses_count", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "regions",
      resourceApiRoute: `regions`,
      deleteSwalTitle: "Вы уверены, что хотите удалить регион?"
    }
  },
}
</script>

<style scoped>

</style>