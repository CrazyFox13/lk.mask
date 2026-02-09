<template>
  <v-data-table
      :headers="headers"
      :items="items"
      :options.sync="options"
      :server-items-length="totalItems"
      :loading="loading"
      class="elevation-1 mt-3"
  >

    <template v-slot:[`item.text`]="{item}">
      <div v-html="item.text"/>
    </template>
    <template v-slot:[`item.created_at`]="{item}">
      {{ moment(item.created_at).format("HH:mm DD.MM.YYYY") }}
    </template>
    <template v-slot:[`item.actions`]="{item}">
      <v-btn color="error" icon @click="onDelete(item)">
        <v-icon>mdi-delete</v-icon>
      </v-btn>
    </template>

  </v-data-table>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2-khonik";

export default {
  name: "CompanyLogs",
  props: ['user_id'],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Текст", value: "text", sortable: true},
        {text: "Дата создания", value: "created_at", sortable: true},
        {text: "", value: "actions", sortable: false},
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
        this.getItems();
      });
    },
  },
  mounted() {
    this.getItems();
  },
  methods: {
    search() {
      this.options.page = 1;
    },
    getItems() {
      this.$http.get(`customers/${this.user_id}/logs?${this.setQueryString(this.query)}`).then(r => {
        this.items = r.body.userLogs;
        this.totalItems = r.body.totalCount;
        this.loading = false;
      })
    },
    onDelete(item){
      Swal.fire({
        title: "Вы действительно хотите удалить запись?",
        showDenyButton: false,
        denyButtonText: `Удалить навсегда`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showConfirmButton: true,
        confirmButtonText: 'Удалить',
        showCloseButton: false,
      }).then(({isDismissed}) => {
        /* Read more about isConfirmed, isDenied below */
        if (isDismissed) return;
        this.$http.delete(`customers/${this.user_id}/logs/${item.id}`).then(() => {
          this.items.splice(this.items.indexOf(item), 1);
        })
      })
    },
  }
}
</script>

<style scoped>

</style>