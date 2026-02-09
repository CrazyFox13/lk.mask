<template>
  <div>
    <div class="text-right">
      <v-btn color="primary" small @click="create()">Создать фильтр</v-btn>
    </div>
    <v-data-table
        :headers="headers"
        :items="items"
        :options.sync="options"
        :server-items-length="totalItems"
        :loading="loading"
        class="elevation-1 mt-3"
        :hide-default-footer="true"
        :hide-default-header="false"
    >
      <template v-slot:[`item.created_at`]="{item}">
        {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
      </template>
      <template v-slot:[`item.active`]="{item}">
        <div class="d-flex">
          <v-icon :color="item.active_email?'success':''">mdi-email-outline</v-icon>
          <v-icon :color="item.active_push?'success':''">mdi-square-rounded-badge-outline</v-icon>
        </div>
      </template>
      <template v-slot:[`item.query`]="{item}">
        <ul>
          <li v-for="i in getParsedQuery(item.query)" :key="i.key">
            {{ i.key }}: <b>{{ i.value }}</b>
          </li>
        </ul>
      </template>
      <template v-slot:[`item.actions`]="{item}">
        <v-btn icon color="warning" @click="edit(item)">
          <v-icon>mdi-pencil</v-icon>
        </v-btn>
        <v-btn icon color="error" @click="destroy(item,false)">
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <v-dialog v-model="editDialog" max-width="1200">
      <OrderFilterEditDialog
          v-if="editDialog"
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
          :user_id="user_id"
      />
    </v-dialog>
  </div>
</template>

<script>
import moment from "moment";
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import OrderFilterEditDialog from "@/components/User/OrderFilterEditDialog";

export default {
  name: "UserOrderFilters",
  components: {OrderFilterEditDialog},
  props: ['user_id'],
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        // {text: "ID", value: "id", sortable: false},
        {text: "Дата создания", value: "created_at", sortable: false},
        {text: "Название", value: "name", sortable: false},
        {text: "Статус", value: "active", sortable: false},
        //  {text: "Фильтры", value: "query", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      moment: moment,

      resourceKey: "orderFilters",
      resourceApiRoute: `order-filters`,
      resourceApiParams: `user_id=${this.user_id}`,
      deleteSwalTitle: "Вы уверены, что хотите удалить фильтр?"
    }
  },
  created() {
    //this.getItems();
  },
  methods: {
    /*getItems() {
      this.$http.get(`order-filters?user_id=${this.user_id}`).then(r => {
        this.items = r.body.orderFilters;
        this.totalItems = r.body.totalCount;
        this.loading = false;
      })
    },*/
    getParsedQuery(query) {
      const obj = JSON.parse(query);
      const arr = Object.keys(obj).map(k => {
        return {
          key: k,
          value: obj[k],
        }
      })
      return arr;
    }
  }
}
</script>

<style scoped>

</style>