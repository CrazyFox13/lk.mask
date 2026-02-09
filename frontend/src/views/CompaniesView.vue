<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Компании</div>
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
                  label="Тип"
                  v-model="query.company_types_id"
                  hide-details
                  :items="companyTypes"
                  item-value="id"
                  item-text="title"
                  multiple
                  clearable
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                  label="Модерация"
                  v-model="query.status"
                  hide-details
                  :items="moderationStatuses"
                  item-value="key"
                  item-text="value"
                  clearable
              />
            </v-col>
            <v-col cols="12" md="3" class="d-flex align-center">
              <VehicleTypeFilter
                  v-model="query.vehicle_types_id"
                  :no-vehicle-value="true"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-checkbox label="Показывать удалённых" v-model="query.show_deleted"/>
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

        <template v-slot:[`item.id`]="{item}">
          <v-btn :color="item.deleted_at?'error':''" text :to="`/companies/${item.id}`">
            {{ item.id }}
          </v-btn>
        </template>
        <template v-slot:[`item.title`]="{item}">
          {{ item.title }}
          <v-tooltip top v-if="item.verified">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                  color="info"
                  v-bind="attrs"
                  v-on="on"
              >mdi-check-circle-outline
              </v-icon>
            </template>
            <span>Верифицировано</span>
          </v-tooltip>
          <v-tooltip top v-if="item.instant_moderation">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                  color="warning"
                  v-bind="attrs"
                  v-on="on"
              >mdi-lightning-bolt
              </v-icon>
            </template>
            <span>Мгновенная модерация</span>
          </v-tooltip>
        </template>
        <template v-slot:[`item.type`]="{item}">
          {{ item.type ? item.type.title : '-' }}
        </template>
        <template v-slot:[`item.vehicle_types_count`]="{item}">
          <v-icon v-if="item.vehicle_types_count>0">mdi-check</v-icon>
        </template>

        <template v-slot:[`item.boss`]="{item}">
          <v-btn text x-small v-if="item.boss" :to="`/users/${item.boss.id}`">
            {{ item.boss.name }} {{ item.boss.surname }}
          </v-btn>
        </template>
        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.membership_fee_paid_at`]="{item}">
          {{ item.membership_fee_paid_at ? moment(item.membership_fee_paid_at).format("DD.MM.YYYY") : '-' }}
        </template>

        <template v-slot:[`item.website`]="{item}">
          <v-btn text x-small v-if="item.website" :href="item.website" target="_blank">{{ item.website }}</v-btn>
        </template>
        <template v-slot:[`item.moderation_status`]="{item}">
          <v-chip small :color="companyStatusLabelColor(item.moderation_status)">
            {{ companyStatusLabelText(item.moderation_status) }}
          </v-chip>
        </template>

        <template v-slot:[`item.actions`]="{item}">
          <v-btn color="error" icon @click="destroy(item)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>
  </v-row>
</template>

<script>

import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import VehicleTypeFilter from "@/components/Common/VehicleTypeFilter";

export default {
  name: "CompaniesView",
  components: {VehicleTypeFilter},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: false},
        {text: "Номер", value: "viewable_reg_number", sortable: false},
        {text: "Дата создания", value: "created_at", sortable: false},
        {text: "Название", value: "title", sortable: false},
        {text: "ИНН", value: "inn", sortable: false},
        {text: "Создатель", value: "boss", sortable: false},
        {text: "Тип", value: "type", sortable: false},
        {text: "Техника", value: "vehicle_types_count", sortable: false},
        {text: "Заявки", value: "active_orders_count", sortable: false},
        {text: "Дата оплаты", value: "membership_fee_paid_at", sortable: false},
        {text: "Статус", value: "moderation_status", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "companies",
      resourceApiRoute: `companies`,
      companyTypes: [],
      deleteSwalTitle: `Вы точно хотите удалить компанию?`,
    }
  },
  computed: {
    moderationStatuses() {
      return [
        {key: '', value: 'Все'},
        ...this.companyModerationStatuses
      ]
    }
  },
  created() {
    this.getCompanyTypes();
  },
  methods: {
    getCompanyTypes() {
      this.$http.get(`company-types`).then(r => {
        this.companyTypes = r.body.companyTypes;
      })
    },
    readRoute() {
      this.query = this.$route.query;
      if (this.query.company_types_id) {
        this.query.company_types_id = this.query.company_types_id.split(",").filter((v, i, self) => self.indexOf(v) === i).map(i => parseInt(i))
      }
      if (typeof this.query.vehicle_types_id === "string") {
        this.query.vehicle_types_id = this.query.vehicle_types_id.split(",").map(Number);
      }
      this.options = this.copyObject({...this.options, ...this.queryToOptions(this.query)});
      this.$nextTick(() => {
        this.getItems();
      })
    }
  }
}
</script>

<style scoped>

</style>