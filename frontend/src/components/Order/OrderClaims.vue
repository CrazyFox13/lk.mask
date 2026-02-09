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

      <template v-slot:[`item.author`]="{item}">
        <v-btn v-if="item.author" small text :to="`/users/${item.author_user_id}`">{{ item.author.name }}
          {{ item.author.surname }}
        </v-btn>
      </template>
    </v-data-table>
    <v-alert v-else type="info" outlined>Подтверждённых жалоб не найдено</v-alert>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "OrderClaims",
  props: ['order_id'],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "Автор", value: "author", sortable: false},
        {text: "Текст", value: "text", sortable: false},
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
      this.$http.get(`claims?status=approved&order_id=${this.order_id}&${this.setQueryString(this.query)}`).then(r => {
        this.items = r.body.claims;
        this.totalItems = r.body.totalCount;
        this.loading = false;
      })
    },
  }
}
</script>

<style scoped>

</style>