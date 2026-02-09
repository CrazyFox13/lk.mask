<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Пользователи</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить пользователя</v-btn>
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
                  :items="queryStatuses"
                  item-value="key"
                  item-text="value"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                  label="Перс. сообщения"
                  v-model="query.personal_notifications"
                  hide-details
                  :items="personalSubscriptions"
                  item-value="key"
                  item-text="value"
              />
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="3">
              <v-checkbox label="Неподтвержден телефон" v-model="query.unconfirmed_phone"/>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="3">
              <v-checkbox label="Незаполнен e-mail" v-model="query.without_email"/>
            </v-col>
            <v-col cols="12" sm="6" md="4" lg="3">
              <v-checkbox label="Неподтвержден e-mail" v-model="query.unconfirmed_email"/>
            </v-col>

            <v-col cols="12" sm="6" md="4" lg="3">
              <v-checkbox label="Показывать удалённых" v-model="query.show_deleted"/>
            </v-col>
            <v-col cols="12" md="3" class="d-flex align-center">
              <VehicleTypeFilter
                  v-model="query.vehicle_types_id"
                  :no-vehicle-value="true"
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

        <template v-slot:[`item.id`]="{item}">
          <v-btn :color="item.deleted_at?'error':''" text :to="`/users/${item.id}`">
            {{ item.id }}
          </v-btn>
        </template>
        <template v-slot:[`item.avatar`]="{item}">
          <v-img
              v-if="item.avatar"
              :src="item.avatar"
              width="50"
              height="50"
              class="rounded-circle"
          />
        </template>

        <template v-slot:[`item.company_role`]="{item}">
          {{ item.company_role === 'boss' ? 'АК' : item.company_id === '' ? 'СК' : '-' }}
        </template>
        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.last_online_datetime`]="{item}">
          {{ item.last_online_datetime ? moment(item.last_online_datetime).format("HH:mm DD.MM.YYYY") : "-" }}
        </template>
        <template v-slot:[`item.email`]="{item}">
          <div class="d-flex align-center">
            {{ item.email }}
            <v-icon color="success" v-if="item.email_verified_at">mdi-check</v-icon>
          </div>
        </template>
        <template v-slot:[`item.phone`]="{item}">
          <div class="d-flex align-center">
            {{ item.phone }}
            <v-icon color="success" v-if="item.phone_verified_at">mdi-check</v-icon>
          </div>
        </template>

        <template v-slot:[`item.city`]="{item}">
          {{ item.city ? item.city.name : '-' }}
        </template>

        <template v-slot:[`item.subscriptions_count`]="{item}">
          <v-icon v-if="item.subscribed_vehicles_count">mdi-tractor</v-icon>
          <v-icon v-if="item.subscribed_cities_count">mdi-city</v-icon>
        </template>

        <template v-slot:[`item.notification_types`]="{item}">
          <div v-if="item.notification_types">
            <div v-for="(type,i) in item.notification_types" :key="i">
              <v-chip x-small :color="type.pivot.active?'primary':''">{{ type.pivot.way }}</v-chip>
            </div>
          </div>
        </template>

        <template v-slot:[`item.comment`]="{item}">
          <div style="cursor: pointer" @click="showComment(item)">
            {{
              item.comment ? `${item.comment.substring(0, 18)}${item.comment.length > 18 ? '...' : ''}` : ''
            }}
          </div>
        </template>

        <template v-slot:[`item.company`]="{item}">
          <v-btn v-if="item.company" small text :to="`/companies/${item.company_id}`">{{ item.company.title }}</v-btn>
        </template>

        <template v-slot:[`item.actions`]="{item}">
          <v-btn color="error" icon @click="destroy(item)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>
    <v-dialog v-model="editDialog" max-width="500">
      <UserCreateDialog
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
      />
    </v-dialog>
  </v-row>
</template>

<script>

import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import VehicleTypeFilter from "@/components/Common/VehicleTypeFilter";
import Swal from "sweetalert2-khonik";
import UserCreateDialog from "@/components/User/UserCreateDialog.vue";

export default {
  name: "UsersView",
  components: {UserCreateDialog, VehicleTypeFilter},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Имя", value: "name", sortable: true},
        {text: "Фамилия", value: "surname", sortable: true},
        {text: "Телефон", value: "phone", sortable: true},
        {text: "E-mail", value: "email", sortable: true},
        {text: "Город", value: "city", sortable: false},
        {text: "Дата регистрации", value: "created_at", sortable: true},
        {text: "Посл. активность", value: "last_online_datetime", sortable: true},
        {text: "Подписка", value: "subscriptions_count", sortable: false},
        {text: "Перс. сообщ.", value: "notification_types", sortable: false},
        {text: "Комментарий", value: "comment", sortable: false},
        {text: "Компания", value: "company", sortable: false},
        {text: "Роль", value: "company_role", sortable: false},
        {text: "Акт. заявки", value: "active_orders_count", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "users",
      resourceApiRoute: `users`,
      queryStatuses: [
        {key: '', value: 'Все'},
        {key: 'without_company', value: 'Без компании'},
        {key: 'boss', value: 'Администратор компании'},
        {key: 'staff', value: 'Сотрудник компании'},
      ],
      personalSubscriptions: [
        {key: '', value: 'Все'},
        {key: 'none', value: 'Без подписки'},
        {key: 'email', value: 'E-mail'},
        {key: 'push', value: 'Push'},
      ],

      deleteSwalTitle: `Вы точно хотите удалить пользователя?`,
    }
  },
  watch: {
    'query.show_deleted': function (v) {
      if (typeof v !== "boolean") {
        this.query.show_deleted = parseInt(v) === 1;
      }
    },
    'query.unconfirmed_phone': function (v) {
      if (typeof v !== "boolean") {
        this.query.unconfirmed_phone = parseInt(v) === 1;
      }
    },
    'query.without_email': function (v) {
      if (typeof v !== "boolean") {
        this.query.without_email = parseInt(v) === 1;
      }
    },
    'query.unconfirmed_email': function (v) {
      if (typeof v !== "boolean") {
        this.query.unconfirmed_email = parseInt(v) === 1;
      }
    },

  },
  methods: {
    modifyQuery() {
      if (typeof this.query.vehicle_types_id === "string") {
        this.query.vehicle_types_id = this.query.vehicle_types_id.split(",").map(Number);
      }
    },
    showComment(item) {
      Swal.fire(item.comment);
    }
  }
}
</script>

<style scoped>

</style>