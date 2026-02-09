<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">FAQ</div>
        <v-spacer/>
        <v-btn small to="/faq/create" color="primary">Добавить элемент</v-btn>
      </div>
    </v-col>
    <v-col cols="12">
      <v-data-table
          :headers="headers"
          :items="items"
          :options="options"
          :loading="loading"
          class="elevation-1 mt-3"
          disable-pagination
          hide-default-footer
      >
        <template v-slot:[`item.hidden`]="{item}">
          <v-icon v-if="!item.hidden">mdi-check</v-icon>
        </template>
        <template v-slot:[`item.move`]="{item,index}">
          <v-btn icon color="info" @click="move('up',item)" :disabled="index===0">
            <v-icon>mdi-arrow-up</v-icon>
          </v-btn>
          <v-btn icon color="info" @click="move('down',item)" :disabled="index>= totalItems-1">
            <v-icon>mdi-arrow-down</v-icon>
          </v-btn>
        </template>
        <template v-slot:[`item.actions`]="{item}">
          <v-btn icon color="warning" :to="`/pages/${item.id}`">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon color="error" class="ml-2" @click="destroy(item)">
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
  name: "FAQ",
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: false},
        {text: "Заголовок", value: "title", sortable: false},
        {text: "Показывается", value: "hidden", sortable: false},
        {text: "", value: "move", sortable: false},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "pages",
      resourceApiRoute: `pages`,
      resourceApiParams: "type=faq",
      deleteSwalTitle: "Вы действительно хотите удалить ресурс?"
    }
  },
  /*computed: {
    sortedItems() {
      return [...this.items].sort((a, b) => a.order - b.order);
    }
  },*/
  methods: {
    move(dir, item) {
      const ids = this.items.map(i => i.id);
      const idx = ids.indexOf(item.id);
      if (idx < 0) return;

      const newIdx = dir === 'up' ? (idx - 1) : (idx + 1);
      this.items.splice(idx, 1); // удаляем элемент из массива
      this.items.splice(newIdx, 0, item); // добавляем в нужное место

      const order = this.items.map(i => i.id)
      this.$http.post(`pages/move`, {
        order: order,
      }).then(() => {

      });
    }
  }
}
</script>

<style scoped>

</style>