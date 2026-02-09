<template>
  <div>
    <div class="d-flex justify-space-between align-center">
      <div class="text-h6">Категории техники</div>
      <div class="d-flex">
        <v-btn small color="info" class="mr-2" link to="/vehicles">Группы</v-btn>
        <v-btn small color="primary" @click="createDialog=true">Добавить категорию</v-btn>
      </div>
    </div>
    <v-row>
      <v-col cols="12">
        <v-data-table
            :headers="headers"
            :items="categories"
            class="elevation-1 mt-3"
        >
          <template v-slot:[`item.id`]="{item}">
            <v-btn text :to="`/vehicle-categories/${item.id}`">
              {{ item.id }}
            </v-btn>
          </template>
          <template v-slot:[`item.color`]="{item}">
            <div v-if="item.color" style="display: block;width: 20px;height: 20px;border-radius: 50px" v-bind:style="{'background-color':item.color}"></div>
          </template>
          <template v-slot:[`item.show_in_menu`]="{item}">
            {{ item.show_in_menu?"Да":"Нет" }}
          </template>
          <template v-slot:[`item.image`]="{item}">
            <v-img v-if="item.image" :src="item.image" height="60" contain/>
          </template>
          <template v-slot:[`item.groups`]="{item}">
            <v-chip-group column>
              <v-chip v-for="group in item.groups" :key="group.id"
                      :to="`/vehicle-groups/${group.id}`">
                {{ group.title }}
              </v-chip>
            </v-chip-group>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
    <v-dialog v-model="createDialog" max-width="400">
      <create-dialog @close="createDialog=false" @created="onCreated"/>
    </v-dialog>
  </div>
</template>

<script>
import CreateDialog from "@/components/Vehicle/CreateCategoryDialog";

export default {
  name: "VehicleCategoriesView",
  components: {CreateDialog},
  data() {
    return {
      categories: [],
      createDialog: false,
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "title", sortable: true},
        {text: "Показывать в меню", value: "show_in_menu", sortable: false},
        {text: "Изображение", value: "image", sortable: false},
        {text: "Цвет фона", value: "color", sortable: false},
        {text: "Группы", value: "groups", sortable: false},
      ],
    }
  },
  created() {
    this.getCategories();
  },
  methods: {
    getCategories() {
      this.$http.get(`vehicle-categories`).then(r => {
        this.categories = r.body.vehicleCategories;
      })
    },
    onCreated(c) {
      this.categories.push(c);
    }
  }
}
</script>

<style scoped>

</style>