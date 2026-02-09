<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Претензии</div>
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
                      ...reportModerationStatuses
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
          <v-badge inline v-if="item.new_messages_count>0" :content="item.new_messages_count">
            <v-btn :color="item.deleted_at?'error':''" text :to="`/reports/${item.id}`">
              {{ item.id }}
            </v-btn>
          </v-badge>
          <v-btn :color="item.deleted_at?'error':''" v-else text :to="`/reports/${item.id}`">
            {{ item.id }}
          </v-btn>
        </template>

        <template v-slot:[`item.created_at`]="{item}">
          {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
        </template>
        <template v-slot:[`item.author`]="{item}">
          <v-btn v-if="item.author" small text :to="`/users/${item.author_user_id}`">{{ item.author.name }}
            {{ item.author.surname }}
          </v-btn>
        </template>
        <template v-slot:[`item.author_company`]="{item}">
          <v-btn v-if="item.author && item.author.company" small text :to="`/companies/${item.author.company.id}`">
            {{ item.author.company.title }}
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

        <template v-slot:[`item.text`]="{item}">
          <span v-html="truncate(item.text,150)"/>
        </template>

        <template v-slot:[`item.status`]="{item}">
          <v-chip small :color="reportStatusLabelColor(item.status)">
            {{ reportStatusLabelText(item.status) }}
          </v-chip>
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
  name: "ReportsView",
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "Автор", value: "author", sortable: false},
        {text: "Компания автора", value: "author_company", sortable: false},
        {text: "Ответчик", value: "target_user", sortable: false},
        {text: "Компания ответчика", value: "company", sortable: false},
        {text: "Текст", value: "text", sortable: false},
        {text: "Ст. претензии", value: "amount", sortable: false},
        {text: "Статус", value: "status", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "reports",
      resourceApiRoute: `reports`,
      deleteSwalTitle: `Вы точно хотите удалить претензию?`,
    }
  },
}
</script>

<style scoped>

</style>