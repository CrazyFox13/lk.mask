<template>
  <div>
    <v-data-table
        v-if="totalItems>0"
        :headers="headers"
        :items="items"
        :options.sync="options"
        :server-items-length="totalItems"
        :loading="loading"
        class="elevation-1 mt-3"
    >
      <template v-slot:[`item.created_at`]="{item}">
        {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
      </template>
      <template v-slot:[`item.status`]="{item}">
        {{ statusMap[item.status] }}
      </template>

      <template v-slot:[`item.user`]="{item}">
        <v-btn v-if="item.user" small text :to="`/users/${item.user_id}`">
          {{ item.user.name }} {{ item.user.surname }}
        </v-btn>
      </template>
      <template v-slot:[`item.company`]="{item}">
        <v-btn v-if="item.company" small text :to="`/companies/${item.company_id}`">
          {{ item.company.title }}
        </v-btn>
      </template>
      <template v-slot:[`item.prices`]="{item}">
        <ul>
          <li>Безналичный расчет с НДС: {{item.amount_account_vat}}</li>
          <li>Безналичный расчет: {{ item.amount_account}}</li>
          <li>Наличные: {{item.amount_cash}}</li>
        </ul>
      </template>
      <template v-slot:[`item.date_start`]="{item}">
        {{ moment(item.date_start).format("DD.MM.YYYY")}}
      </template>
      <template v-slot:[`item.decline_reason`]="{item}">
        <v-chip 
          v-if="item.status === 'declined' && item.decline_reason" 
          small 
          color="orange" 
          text-color="white"
        >
          {{ declineReasonMap[item.decline_reason] || item.decline_reason }}
        </v-chip>
        <span v-else-if="item.status === 'declined'" class="text--disabled">Не указана</span>
        <span v-else class="text--disabled">-</span>
      </template>

    </v-data-table>
    <v-alert v-else type="info" outlined>Предложений не найдено</v-alert>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "OrderOffers",
  props: ['order_id'],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: false},
        {text: "Дата создания", value: "created_at", sortable: false},
        {text: "Статус", value: "status", sortable: false},
        {text: "Автор", value: "user", sortable: false},
        {text: "Компания", value: "company", sortable: false},
        {text: "Предложение", value: "prices", sortable: false},
        {text: "Дата старта", value: "date_start", sortable: false},
        {text: "Комментарий", value: "comment", sortable: false},
        {text: "Причина отказа от поставщика", value: "decline_reason", sortable: false},
      ],
      items: [],
      options: {},
      totalItems: 0,
      loading: true,
      query: {},
      errors: {},
      moment: moment,

      statusMap:{
        waiting:"На рассмотрении",
        accepted:"Принято",
        declined:"Отклонено",
      },
      declineReasonMap: {
        'works_canceled': 'Отменились работы',
        'found_equipment': 'Нашли технику',
        'high_price': 'Высокая стоимость',
        'bad_terms': 'Не устроили сроки',
        'other': 'Другое'
      },
    }
  },
  watch: {
    options(v) {
      this.query = this.copyObject({...this.query, ...this.optionsToQuery(v)});
      this.$nextTick(() => {
        this.replaceRoute();
      });
    },
    "$route": {
      handler() {
        this.readRoute();
      }, deep: true
    },
  },
  mounted() {
    this.readRoute();
  },
  methods: {
    search() {
      this.options.page = 1;
      this.replaceRoute();
    },
    readRoute() {
      this.query = this.$route.query;
      this.options = this.copyObject({...this.options, ...this.queryToOptions(this.query)});
      this.$nextTick(() => {
        this.getItems();
      })
    },
    replaceRoute() {
      this.$router.replace(`${this.$route.path}?${this.setQueryString(this.query)}`).catch(() => {
      });
    },
    getItems() {
      this.$http.get(`orders/${this.order_id}/order-offers?${this.setQueryString(this.query)}`).then(r => {
        this.items = r.body.orderOffers;
        this.totalItems = r.body.totalCount;
        this.loading = false;
      })
    },
  }
}
</script>

<style scoped>

</style>