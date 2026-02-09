<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Жалобы</div>
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
                  :items="[
                      {key:'',value:'Все'},
                      ...claimModerationStatuses
                  ]"
                  item-value="key"
                  item-text="value"
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
          <span v-bind:class="{'error--text':!!item.deleted_at}">{{ item.id }}</span>
        </template>
        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.author`]="{item}">
          <v-btn v-if="item.author" small text :to="`/users/${item.author_user_id}`">{{ item.author.name }}
            {{ item.author.surname }}
          </v-btn>
        </template>
        <template v-slot:[`item.order`]="{item}">
          <router-link v-if="item.order_id" :to="`/orders/${item.order_id}`">
            {{ item.order ? item.order.title : 'Заказ удалён' }}
          </router-link>
        </template>
        <template v-slot:[`item.documents`]="{item}">
          <div v-if="item.documents.length>0">
            <a v-for="(document,i) in item.documents" :key="i" :href="document.url" target="_blank">
              Вложение №{{i+1}}
            </a>
          </div>
        </template>

        <template v-slot:[`item.status`]="{item}">
          <v-select
              @change="moderate(item)"
              dense
              hide-details
              label=""
              v-model="item.status"
              :items="claimModerationStatuses"
              item-text="value"
              item-value="key"/>
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

export default {
  name: "ClaimsView",
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "Автор", value: "author", sortable: false},
        {text: "Заказ", value: "order", sortable: false},
        {text: "Текст", value: "text", sortable: false},
        {text: "Вложения", value: "documents", sortable: false},
        {text: "Статус", value: "status", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "claims",
      resourceApiRoute: `claims`,
      deleteSwalTitle: `Вы точно хотите удалить жалобу?`,
    }
  },
  methods: {
    async moderate(item) {
      let method;
      switch (item.status) {
        case "draft":
          method = 'draft'
          break;
        case "approved":
          method = 'approve';
          break;
        case "viewed":
          method = 'view';
          break;
        case "canceled":
          method = 'cancel';
          break;
        default:
          return;
      }
      await this.$http.post(`claims/${item.id}/${method}`);
    }
  }
}
</script>

<style scoped>

</style>