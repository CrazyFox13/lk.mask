<template>
  <v-data-table
      :headers="headers"
      :items="items"
      :options.sync="options"
      :server-items-length="totalItems"
      :loading="loading"
      class="elevation-1 mt-3"
  >
    <template v-slot:[`item.user`]="{item}">
      <v-btn text x-small v-if="item.user" :to="`/users/${item.user.id}`">
        {{ item.user.name }} {{ item.user.surname }}
      </v-btn>
    </template>
    <template v-slot:[`item.id`]="{item}">
      <v-btn text :to="`/orders/${item.id}`">
        {{item.id}}
      </v-btn>
    </template>
    <template v-slot:[`item.created_at`]="{item}">
      {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
    </template>
    <template v-slot:[`item.updated_at`]="{item}">
      {{ item.updated_at ? moment(item.updated_at).format("HH:mm DD.MM.YYYY") : '-' }}
    </template>
    <template v-slot:[`item.moderation_status`]="{item}">
      <v-chip small :color="orderStatusLabelColor(item.moderation_status)">
        {{ orderStatusLabelText(item.moderation_status) }}
      </v-chip>
    </template>
  </v-data-table>
</template>

<script>
import moment from "moment";

export default {
  name: "CompanyOrders",
  props:['company_id'],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "title", sortable: true},
        {text: "Статус", value: "moderation_status", sortable: false},
        {text: "Пользователь", value: "user", sortable: false},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "Дата изменения", value: "updated_at", sortable: true},
      ],
      items: [],
      options: {},
      totalItems: 0,
      loading: true,
      query: {},
      errors: {},
      moment: moment,
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
  computed: {
    moderationStatuses() {
      return [
        {key: '', value: 'Все'},
        ...this.orderModerationStatuses,
      ]
    }
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
      if (this.query.vehicle_types_id) {
        this.query.vehicle_types_id = this.query.vehicle_types_id.split(",").filter((v, i, self) => self.indexOf(v) === i).map(i => parseInt(i))
      }
      this.options = this.copyObject({...this.options, ...this.queryToOptions(this.query)});
      this.$nextTick(() => {
        this.getItems();
      })
    },
    replaceRoute() {
      this.$router.replace(`/companies/${this.company_id}?${this.setQueryString(this.query)}`).catch(() => {
      });
    },
    getItems() {
      this.$http.get(`orders?company_id=${this.company_id}&${this.setQueryString(this.query)}`).then(r => {
        this.items = r.body.orders;
        this.totalItems = r.body.totalCount;
        this.loading = false;
      })
    },
  }
}
</script>

<style scoped>

</style>