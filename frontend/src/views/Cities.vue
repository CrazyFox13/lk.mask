<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Города</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить город</v-btn>
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
      <CityEditDialog
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
          :region_id="region_id"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import CityEditDialog from "@/components/GEO/CityEditDialog";
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";

export default {
  name: "CitiesView",
  components: {CityEditDialog},
  data() {
    return {
      region_id: this.$route.params.region_id,
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "name", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "cities",
      resourceApiRoute: `regions/${this.$route.params.region_id}/cities`,
      deleteSwalTitle: "Вы уверены, что хотите удалить город?"
    }
  },
  mixins: [ResourceComponentHelper],
}
</script>

<style scoped>

</style>