<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Модераторы</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить модератора</v-btn>
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

        <template v-slot:[`item.edit`]="{item}">
          <v-tooltip top>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="edit(item)" icon small color="warning" v-bind="attrs" v-on="on">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <span>Редактировать</span>
          </v-tooltip>
        </template>
        <template v-slot:[`item.reset_password`]="{item}">
          <v-tooltip top>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="resetPassword(item)" icon small color="info" v-bind="attrs" v-on="on">
                <v-icon>mdi-lock-reset</v-icon>
              </v-btn>
            </template>
            <span>Сбросить пароль</span>
          </v-tooltip>
        </template>
        <template v-slot:[`item.delete`]="{item}">
          <v-tooltip top>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="destroy(item)" icon small color="error" v-bind="attrs" v-on="on">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
            <span>Удалить</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-col>

    <v-dialog v-model="editDialog" max-width="500">
      <ModeratorEditDialog
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import ModeratorEditDialog from "@/components/Moderator/ModeratorEditDialog";
import Swal from "sweetalert2-khonik";
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";

export default {
  name: "ModeratorsView",
  components: {ModeratorEditDialog},
  mixins:[ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Имя", value: "name", sortable: true},
        {text: "Фамилия", value: "surname", sortable: true},
        {text: "Телефон", value: "phone", sortable: true},
        {text: "Email", value: "email", sortable: true},
        {text: "", value: "edit", sortable: false},
        {text: "", value: "reset_password", sortable: false},
        {text: "", value: "delete", sortable: false},
      ],
      resourceKey: "users",
      resourceApiRoute: `moderators`,
      deleteSwalTitle: "Вы действительно хотите удалить модератора?"
    }
  },
  methods: {
    resetPassword(item) {
      Swal.fire({
        title: 'Вы действительно хотите сбросить пароль?',
        showDenyButton: true,
        denyButtonText: `Сбросить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.post(`moderators/${item.id}/reset-password`).then(r => {
            Swal.fire({
              title: 'Пароль от учётной записи',
              text: r.body.plainPassword,
            })
          })
        }
      })
    },
  }
}
</script>

<style scoped>

</style>