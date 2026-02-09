<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Заявки</div>
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
            <v-col cols="12" md="3" class="d-flex align-center">
              <VehicleTypeFilter
                  v-model="query.vehicle_types_id"
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
      <div>
        Выбрано: {{ selectedOrdersId.length }} &nbsp;
        <v-btn
            @click="moderationDialog=true"
            small
            class="ml-2"
            color="info"
            :disabled="selectedOrdersId.length===0"
        >Изменить статус
        </v-btn>
        <v-btn
            @click="multipleDestroy()"
            small
            class="ml-2"
            color="error"
            :disabled="selectedOrdersId.length===0"
        >Удалить
        </v-btn>
      </div>
      <v-data-table
          :headers="headers"
          :items="items"
          :options.sync="options"
          :server-items-length="totalItems"
          :loading="loading"
          class="elevation-1 mt-3"
          show-select
          v-model="selectedOrders"
      >

        <template v-slot:[`item.id`]="{item}">
          <v-btn :color="item.deleted_at?'error':''" text :to="`/orders/${item.id}`">
            {{ item.id }}
          </v-btn>
        </template>

        <template v-slot:[`item.user`]="{item}">
          <v-btn text x-small v-if="item.user" :to="`/users/${item.user.id}`">
            {{ item.user.name }} {{ item.user.surname }}
          </v-btn>
          <i v-else>Пользователь удалён</i>
        </template>
        <template v-slot:[`item.company`]="{item}">
          <v-btn text x-small v-if="item.company" :to="`/companies/${item.company.id}`">{{ item.company.title }}</v-btn>
          <i v-else>Компания не найдена</i>
        </template>
        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.updated_at`]="{item}">
          {{ item.updated_at ? moment(item.updated_at).format("HH:mm DD.MM.YYYY") : '-' }}
        </template>
        <template v-slot:[`item.publish_date`]="{item}">
          {{ item.publish_date ? moment(item.publish_date).format("HH:mm DD.MM.YYYY") : '-' }}
        </template>

        <template v-slot:[`item.moderation_status`]="{item}">
          <v-chip small :color="orderStatusLabelColor(item.moderation_status)">
            {{ orderStatusLabelText(item.moderation_status) }}
          </v-chip>
        </template>

        <template v-slot:[`item.actions`]="{item}">
          <v-btn color="error" icon @click="destroy(item)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>

    <v-dialog v-model="moderationDialog" max-width="400">
      <MultipleOrderModeration
          :ids="selectedOrdersId"
          @close="moderationDialog = false;"
          @changed="getItems()"
      />
    </v-dialog>
  </v-row>
</template>

<script>

import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import Swal from "sweetalert2-khonik";
import MultipleOrderModeration from "@/components/Order/MultipleOrderModeration";
import VehicleTypeFilter from "@/components/Common/VehicleTypeFilter";

export default {
  name: "OrdersView",
  components: {VehicleTypeFilter, MultipleOrderModeration},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "title", sortable: true},
        {text: "Статус", value: "moderation_status", sortable: false},
        {text: "Пользователь", value: "user", sortable: false},
        {text: "Компания", value: "company", sortable: false},
        {text: "Предложения", value: "offers_count", sortable: false},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "Дата изменения", value: "updated_at", sortable: true},
        {text: "Дата публикации", value: "publish_date", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "orders",
      resourceApiRoute: `orders`,
      deleteSwalTitle: `Вы точно хотите удалить заявку?`,
      selectedOrders: [],
      moderationDialog: false,
    }
  },
  computed: {
    moderationStatuses() {
      return [
        {key: '', value: 'Все'},
        ...this.orderModerationStatuses,
      ]
    },
    selectedOrdersId() {
      return this.selectedOrders.map(i => i.id);
    }
  },
  methods: {
    readRoute() {
      this.query = this.$route.query;
      if (this.query.vehicle_types_id) {
        this.query.vehicle_types_id = this.query.vehicle_types_id.split(",").filter((v, i, self) => self.indexOf(v) === i).map(i => parseInt(i))
      }
      this.options = this.copyObject({...this.options, ...this.queryToOptions(this.query)});
      this.$nextTick(() => {
        this.getItems();
      })
    },
    multipleDestroy() {
      Swal.fire({
        title: `Вы уверены, что хотите удалить ${this.selectedOrdersId.length} заявкок?`,
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.post(`orders/multiple-delete`, {
            ids: this.selectedOrdersId,
          }).then(() => {
            this.selectedOrders = [];
            this.getItems();
          })
        }
      })
    },
  }
}
</script>

<style scoped>

</style>