<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Отчёт по заявкам</div>
    </v-col>
    <v-col cols="12">
      <v-card>
        <v-card-title>Фильтр</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="4">
              <CompanyPicker v-model="query.company_id"/>
            </v-col>
            <v-col cols="12" md="3">
              <DatePicker v-model="query.date_start" label="Дата начала"/>
            </v-col>
            <v-col cols="12" md="4">
              <DatePicker v-model="query.date_end" label="Дата завершения"/>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="search()" :disabled="!requestIsReady">Сформировать</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>
    <v-col cols="12">
      <v-data-table
          :headers="headers"
          :items="items"
          class="elevation-1 mt-3"
          :items-per-page="-1"
      >
        <template v-slot:[`item.employee`]="{item}">
          {{ item.name }} {{ item.surname }}
        </template>
        <template v-slot:[`item.last_order_created_at`]="{item}">
          {{ item.last_order ? moment(item.last_order.created_at).format("HH:mm DD.MM.YYYY") : "-" }}
        </template>
      </v-data-table>
    </v-col>
  </v-row>
</template>

<script>

import CompanyPicker from "@/components/Common/CompanyPicker.vue";
import DatePicker from "@/components/Common/Datepicker.vue";
import moment from "moment";

export default {
  name: "AdminReportOrders",
  components: {DatePicker, CompanyPicker},
  data() {
    return {
      moment: moment,
      headers: [
        {text: "Сотрудник", value: "employee", sortable: false},
        {text: "Заявки", value: "orders_count", sortable: false},
        {text: "Активные", value: "active_orders_count", sortable: false},
        {text: "Закрытые", value: "finished_orders_count", sortable: false},
        {text: "Исполнитель найден", value: "completed_orders_count", sortable: false},
        {text: "Дата создания", value: "last_order_created_at", sortable: false},
      ],
      query: {
        company_id: null,
        date_start: null,
        date_end: null,
      },
      items: [],
    }
  },
  computed: {
    requestIsReady() {
      return this.query.company_id && this.query.date_start && this.query.date_end
    }
  },
  methods: {
    search() {
      this.$http.post(`reports/employee-order`, this.query).then(({body}) => {
        this.items = body.employees;
        this.$nextTick(() => {
          this.isLoading = false;
        })
      })
    },
  }
}
</script>


<style scoped>

</style>