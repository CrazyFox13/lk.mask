<template>
  <div>
    <v-progress-circular indeterminate v-if="loading"/>
    <v-row v-else>
      <v-col cols="12" md="4">
        <v-sheet elevation="3" class="px-4 py-2" height="100%">
          <div class="text-subtitle-1">Пользователи</div>
          <div class="text-h6">
            Всего: <a class="dashboard-numbers" href="/admin/users">{{ data.users.total }}</a>
          </div>
          <div class="text-h6">
            Онлайн: <a class="dashboard-numbers" href="/admin/users?online=1">{{ data.users.online }}</a>
          </div>
        </v-sheet>
      </v-col>
      <v-col cols="12" md="4">
        <v-sheet elevation="3" class="px-4 py-2" height="100%">
          <div class="text-subtitle-1">Компании</div>
          <div class="text-h6">
            Опубликовано: <a class="dashboard-numbers" href="/admin/companies?status=approved">{{
              data.companies.active
            }}</a>
          </div>
          <div class="text-h6">
            На модерации:
            <a class="dashboard-numbers" href="/admin/companies?status=moderation">
              {{ data.companies.moderation }}
            </a>
          </div>
        </v-sheet>
      </v-col>
      <v-col cols="12" md="4">
        <v-sheet elevation="3" class="px-4 py-2" height="100%">
          <div class="text-subtitle-1">Заявки</div>
          <div class="text-h6">
            Всего: <a class="dashboard-numbers" href="/admin/orders">{{ data.orders.total }}</a>
          </div>
          <div class="text-h6">
            Опубликовано: <a class="dashboard-numbers" href="/admin/orders">{{ data.orders.active }}</a>
          </div>
          <div class="text-h6">
            На модерации: <a class="dashboard-numbers" href="/admin/orders?status=moderation">{{
              data.orders.moderation
            }}</a>
          </div>
        </v-sheet>
      </v-col>
      <v-col cols="12" md="4">
        <v-sheet elevation="3" class="px-4 py-2" height="100%">
          <div class="text-subtitle-1">Претензии</div>
          <div class="text-h6">
            Новые: <a class="dashboard-numbers" href="/admin/reports?status=draft">{{ data.reports.draft }}</a>
          </div>
          <div class="text-h6">
            В работе: <a class="dashboard-numbers" href="/admin/reports?status=active">{{ data.reports.active }}</a>
          </div>
          <div class="text-h6">
            Вызван арбитр: <a class="dashboard-numbers" href="/admin/reports?status=referee">{{
              data.reports.referee
            }}</a>
          </div>
        </v-sheet>
      </v-col>
      <v-col cols="12" md="4">
        <v-sheet elevation="3" class="px-4 py-2" height="100%">
          <div class="text-subtitle-1">Жалобы</div>
          <div class="text-h6">
            Новые: <a class="dashboard-numbers" href="/admin/claims?status=draft">{{ data.claims.draft }}</a>
          </div>
          <div class="text-h6">
            Требуют решения: <a class="dashboard-numbers" href="/admin/claims?status=viewed">{{
              data.claims.viewed
            }}</a>
          </div>
        </v-sheet>
      </v-col>
      <v-col cols="12" md="4">
        <v-sheet elevation="3" class="px-4 py-2" height="100%">
          <div class="text-subtitle-1">Рекомендации</div>
          <div class="text-h6">
            Новые:
            <a class="dashboard-numbers" href="/admin/recommendations?status=draft">
              {{ data.recommendations.draft }}
            </a>
          </div>
          <div class="text-h6">
            Требуют решения:
            <a class="dashboard-numbers" href="/admin/recommendations?status=viewed">
              {{ data.recommendations.viewed }}
            </a>
          </div>
        </v-sheet>
      </v-col>
      <v-col cols="12">
        <div>
          <date-picker label="Выберите диапазон дат" v-model="dates" :range="true" @selected="getData()"/>
        </div>
        <v-row>
          <v-col cols="8">
            <v-row>
              <v-col cols="12">Пользователи</v-col>
              <v-col cols="12" md="6">
                <users-registrations-graph :registrations="data.users_graph" :online_graph="data.online_graph"/>
              </v-col>
              <v-col cols="12" md="6">
                <company-types :types="data.company_types"/>
              </v-col>
              <v-col cols="12">Заявки</v-col>
              <v-col cols="12">
                <orders-creating-graph style="height: 300px" :orders="data.orders_graph"/>
              </v-col>
              <v-col cols="12">
                <orders-graph :types="data.order_vehicle_type_graph"/>
              </v-col>
              <v-col cols="12">Чаты</v-col>
              <v-col cols="12" md="6">
                <NewChatsGraph :new_chats="data.new_chats"/>
              </v-col>
              <v-col cols="12" md="6">
                <NewMessagesGraph :new_messages="data.new_messages"/>
              </v-col>
              <v-col cols="12">Устройства</v-col>
              <v-col cols="12" md="6">
                <devices-graph :devices="data.devices"/>
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="4">
            <div class="text-subtitle-1">Техника компаний</div>
            <v-list>
              <v-list-group v-for="group in data.vehicle_groups" :key="group.id">
                <template v-slot:activator>
                  <v-list-item-title>{{ group.title }}</v-list-item-title>
                  <v-list-item-icon>
                    {{ group.published_companies_count }}
                  </v-list-item-icon>
                </template>
                <v-list-item
                    v-for="type in group.types"
                    :key="type.id"
                    link
                    :to="`/companies?status=approved&vehicle_types_id=${type.id}`">
                  <v-list-item-title>{{ type.title }}</v-list-item-title>
                  <v-list-item-icon>
                    {{ type.published_companies_count }}
                  </v-list-item-icon>
                </v-list-item>
              </v-list-group>
            </v-list>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>

import OrdersGraph from "@/components/Home/OrdersGraph";
import UsersRegistrationsGraph from "@/components/Home/UsersRegistrationsGraph";
import OrdersCreatingGraph from "@/components/Home/OrdersCreatingGraph";
import CompanyTypes from "@/components/Home/CompanyTypes";
import DatePicker from "@/components/Common/Datepicker";
import DevicesGraph from "@/components/Home/DevicesGraph";
import NewChatsGraph from "@/components/Home/NewChatsGraph";
import NewMessagesGraph from "@/components/Home/NewMessagesGraph";

export default {
  name: 'HomeView',
  components: {
    NewMessagesGraph,
    NewChatsGraph,
    DevicesGraph, DatePicker, CompanyTypes, OrdersCreatingGraph, UsersRegistrationsGraph, OrdersGraph
  },
  data() {
    return {
      data: {},
      loading: true,
      dates: []
    }
  },
  created() {
    this.getData();
  },
  computed: {
    datesRequest() {
      const dates = this.dates.filter(Boolean);
      dates.sort();
      return dates.join(",")
    }
  },
  methods: {
    getData() {
      this.loading = true;
      this.$http.get(`dashboard?order_ids=18,44&date_range=${this.datesRequest}`).then(r => {
        this.data = r.body.data;
        this.$nextTick(() => {
          this.loading = false;
        })
      })
    }
  }
}
</script>
<style scoped>
.dashboard-numbers {
  cursor: pointer;
  color: inherit !important;
  text-decoration: none;
  font-weight: bold;
}

.dashboard-numbers:hover {
  text-decoration: underline;
}
</style>