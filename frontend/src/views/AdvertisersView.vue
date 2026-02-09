<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Рекламодатели</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить</v-btn>
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
          disable-pagination
      >
        <template v-slot:[`item.is_active`]="{item}">
          <v-icon v-if="item.is_active">mdi-check</v-icon>
        </template>
        <template v-slot:[`item.start_date`]="{item}">
          {{ moment(item.start_date).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.end_date`]="{item}">
          {{ moment(item.end_date).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.banners_count`]="{item}">
          <router-link :to="`/adv-banners?advertiser_id=${item.id}`">
            {{ item.banners_count }}
          </router-link>
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
      <AdvertiserEditDialog
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
import AdvertiserEditDialog from "@/components/Advertiser/AdvertiserEditDialog";

export default {
  name: "AdvertisersView",
  components: {AdvertiserEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "name", sortable: true},
        {text: "ИНН", value: "inn", sortable: true},
        {text: "Показывается", value: "is_active", sortable: true},
        {text: "Старт показа", value: "start_date", sortable: true},
        {text: "Завершение показа", value: "end_date", sortable: true},
        {text: "Кол-во баннеров", value: "banners_count", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "advertisers",
      resourceApiRoute: `advertisers`,
      deleteSwalTitle: "Вы действительно хотите удалить рекламодателя?"
    }
  },
}
</script>

<style scoped>

</style>