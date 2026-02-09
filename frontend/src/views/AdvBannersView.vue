<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Баннеры</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Создать</v-btn>
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
            <v-col cols="12" md="4">
              <AdvPlacePicker
                  v-model="query.adv_place_id"
              />
            </v-col>
            <v-col cols="12" md="4">
              <AdvertiserPicker
                  v-model="query.advertiser_id"
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
        <template v-slot:[`item.img_url`]="{item}">
          <v-img :src="item.img_url" height="80" width="80" contain/>
        </template>
        <template v-slot:[`item.adv_place_id`]="{item}">
          {{ item.place.name }}
        </template>
        <template v-slot:[`item.advertiser_id`]="{item}">
          {{ item.advertiser.name }}
        </template>
        <template v-slot:[`item.is_active`]="{item}">
          <v-icon v-if="item.is_active">mdi-check</v-icon>
        </template>
        <template v-slot:[`item.start_date`]="{item}">
          {{ moment(item.start_date).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.end_date`]="{item}">
          {{ moment(item.end_date).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.showing`]="{item}">
          <v-icon v-if="item.showing" color="success">mdi-check-circle-outline</v-icon>
          <v-icon v-else color="error">mdi-close-circle-outline</v-icon>
        </template>
        <template v-slot:[`item.vehicle_types`]="{item}">
          {{ item.vehicle_types.map(i => i.title).join(', ') }}
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
      <AdvBannerEditDialog
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
import AdvBannerEditDialog from "@/components/AdvBanner/AdvBannerEditDialog";
import AdvPlacePicker from "@/components/Common/AdvPlacePicker";
import AdvertiserPicker from "@/components/Common/AdvertiserPicker";

export default {
  name: "AdvertisersView",
  components: {AdvertiserPicker, AdvPlacePicker, AdvBannerEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "title", sortable: true},
        {text: "Изображение", value: "img_url", sortable: true},
        {text: "Вкл", value: "is_active", sortable: true},
        {text: "Старт показа", value: "start_date", sortable: true},
        {text: "Завершение показа", value: "end_date", sortable: true},
        {text: "Показывается", value: "showing", sortable: true},
        {text: "Место", value: "adv_place_id", sortable: false},
        {text: "Техника", value: "vehicle_types", sortable: false},
        {text: "Рекламодатель", value: "advertiser_id", sortable: false},
        {text: "URL", value: "endpoint_url", sortable: false},
        {text: "ERID", value: "token", sortable: false},
        {text: "Показы", value: "views", sortable: true},
        {text: "Клики", value: "clicks", sortable: true},
        {text: "CTR", value: "ctr", sortable: false},
        {text: "Комментарий", value: "comment", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "advBanners",
      resourceApiRoute: `adv-banners`,
      deleteSwalTitle: "Вы действительно хотите удалить баннер?"
    }
  },
}
</script>

<style scoped>

</style>