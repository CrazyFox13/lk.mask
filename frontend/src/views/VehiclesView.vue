<template>
  <div>
    <div class="d-flex justify-space-between align-center">
      <div class="text-h6">Группы техники</div>
      <div class="d-flex">
        <v-btn small color="info" class="mr-2" link to="/vehicle-categories">Категории</v-btn>
        <v-btn small color="primary" @click="createDialog=true">Добавить группу</v-btn>
      </div>
    </div>
    <v-row>
      <v-col cols="12">
        <v-data-table
            :headers="headers"
            :items="groups"
            class="elevation-1 mt-3"
        >
          <template v-slot:[`item.id`]="{item}">
            <v-btn text :to="`/vehicle-groups/${item.id}`">
              {{ item.id }}
            </v-btn>
          </template>
          <template v-slot:[`item.category`]="{item}">
            {{ item.category?.title || "-"}}
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
          <template v-slot:[`item.logo`]="{item}">
            <v-img v-if="item.logo" :src="item.logo" width="40" height="40"/>
          </template>
          <template v-slot:[`item.types`]="{item}">
            <v-chip-group column>
              <v-chip v-for="type in item.types" :key="type.id"
                      :to="`/vehicle-groups/${item.id}/vehicle-types/${type.id}`">
                {{ type.title }}
              </v-chip>
            </v-chip-group>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
    <v-dialog v-model="createDialog" max-width="400">
      <create-dialog @close="createDialog=false" @created="onGroupCreated"/>
    </v-dialog>
  </div>
</template>

<script>
import CreateDialog from "@/components/Vehicle/CreateGroupDialog";

export default {
  name: "VehiclesView",
  components: {CreateDialog},
  data() {
    return {
      groups: [],
      createDialog: false,
      headers: [
        // {text: "Действия", value: "actions", sortable: false},
        // {text: "Аватар", value: "avatar", sortable: false},
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "title", sortable: true},
        {text: "Лого", value: "logo", sortable: false},
        {text: "Категория", value: "category", sortable: false},
        {text: "Показывать в меню", value: "show_in_menu", sortable: false},
        {text: "Изображение меню", value: "image", sortable: false},
        {text: "Цвет меню", value: "color", sortable: false},
        {text: "Типы", value: "types", sortable: false},
      ],
    }
  },
  created() {
    this.getGroups();
  },
  methods: {
    getGroups() {
      this.$http.get(`vehicle-groups`).then(r => {
        this.groups = r.body.vehicleGroups;
      })
    },
    onGroupCreated(group) {
      this.groups.push(group);
    }
  }
}
</script>

<style scoped>

</style>