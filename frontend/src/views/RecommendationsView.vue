<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Рекомендации</div>
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
                      ...recommendationModerationStatuses
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
          <span v-bind:class="{'error--text':!!item.deleted_at}">{{item.id}}</span>
        </template>
        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.author`]="{item}">
          <v-btn v-if="item.author" small text :to="`/users/${item.author_user_id}`">{{ item.author.name }}
            {{ item.author.surname }}
          </v-btn>
        </template>
        <template v-slot:[`item.target_user`]="{item}">
          <v-btn v-if="item.target_user" small text :to="`/users/${item.target_user_id}`">{{ item.target_user.name }}
            {{ item.target_user.surname }}
          </v-btn>
        </template>

        <template v-slot:[`item.company`]="{item}">
          <v-btn v-if="item.company" small text :to="`/companies/${item.company_id}`">{{ item.company.title }}</v-btn>
        </template>

        <template v-slot:[`item.status`]="{item}">
          <v-select
              @change="moderate(item)"
              dense
              hide-details
              label=""
              v-model="item.status"
              :items="recommendationModerationStatuses"
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
  name: "RecommendationsView",
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "Автор", value: "author", sortable: false},
        {text: "Цель", value: "target_user", sortable: false},
        {text: "Компания", value: "company", sortable: false},
        {text: "Текст", value: "text", sortable: false},
        {text: "Модерация", value: "status", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "recommendations",
      resourceApiRoute: `recommendations`,
      deleteSwalTitle: `Вы точно хотите удалить рекомендацию?`,
    }
  },
  methods: {
    async moderate(item) {
      let method;
      switch (item.status) {
        case "draft":
          method = 'draft'
          break;
        case "viewed":
          method = 'view';
          break;
        case "approved":
          method = 'approve';
          break;
        case "canceled":
          method = 'cancel';
          break;
        case "deleted":
          method = 'archive';
          break;
        default:
          return;
      }
      await this.$http.post(`recommendations/${item.id}/${method}`);
    }
  }
}
</script>

<style scoped>

</style>