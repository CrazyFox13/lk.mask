<template>
  <div>
    <v-breadcrumbs :items="breadcrumps" divider="-"/>
    <div class="text-h6 d-flex align-center">
      <v-img max-width="20" height="20" :src="group.logo"/>
      <div class="ml-1">{{ group.title }}</div>
    </div>

    <v-row>
      <v-col cols="12" md="6">
        <div class="d-flex justify-space-between align-center mt-6 mb-4">
          <div class="text-subtitle-1">Изменить группу</div>
        </div>
        <VehicleCategoryPicker
            v-model="editGroup.vehicle_category_id"
        />
        <v-text-field
            label="Название"
            v-model="editGroup.title"
            :error="!!errors.title"
            :error-count="1"
            :error-messages="errors.title"
        />
        <file-uploader
            label="Логотип"
            v-model="editGroup.logo"
            :error="errors.logo"
        />


        <v-switch
            label="Показывать в меню"
            v-model="editGroup.show_in_menu"
            :error="!!errors.show_in_menu"
            :error-count="1"
            :error-messages="errors.show_in_menu"
        />


        <div v-if="editGroup.image">
          <v-img :src="editGroup.image" width="120" contain/>
        </div>
        <file-uploader
            v-if="editGroup.show_in_menu"
            label="Изображение для меню"
            v-model="editGroup.image"
            :error="errors.image"
        />

        <div v-if="editGroup.show_in_menu">
          <label class="text-gray">Цвет фона в меню</label>
          <v-color-picker
              :value="editGroup.color || ''"
              @update:color="(v)=>editGroup.color = v.hexa"
          />
        </div>

        <div class="d-flex justify-space-between align-center mt-2">
          <v-btn small color="error" @click="destroy()">Удалить</v-btn>
          <v-btn color="primary" @click="update()">Изменить</v-btn>
        </div>
      </v-col>
      <v-col cols="12" md="6">
        <div class="d-flex justify-space-between align-center mt-6 mb-4">
          <div class="text-subtitle-1">Наименование техники</div>
          <v-btn small color="primary" @click="createDialog=true">Добавить наименование</v-btn>
        </div>
        <v-alert v-if="types.length===0" type="info" outlined>Наименования техники не добавлены.</v-alert>
        <v-list v-else>
          <v-list-item v-for="type in types" :key="type.id">
            <v-list-item-avatar>
              <v-img :src="type.image"/>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title>{{ type.title }}</v-list-item-title>
              <v-list-item-subtitle>Заявок: {{ type.orders_count }}</v-list-item-subtitle>
              <v-list-item-subtitle v-if="type.show_in_menu">
                <div class="d-flex align-items-center">
                  <span
                      class="mr-1"
                      style="display:inline-block;width: 18px;height: 18px;border-radius: 50px"
                      v-bind:style="{'background-color':type.color}"></span>
                  Показывать в меню
                </div>
              </v-list-item-subtitle>
            </v-list-item-content>
            <v-list-item-action>
              <v-btn icon :to="`/vehicle-groups/${group.id}/vehicle-types/${type.id}`">
                <v-icon>mdi-eye</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </v-list>
      </v-col>
    </v-row>

    <v-dialog v-model="createDialog" max-width="400">
      <create-type-dialog :group="group" @close="createDialog=false" @created="onTypeCreated"/>
    </v-dialog>
  </div>
</template>

<script>
import FileUploader from "@/components/Common/FileUploader";
import Swal from "sweetalert2-khonik";
import CreateTypeDialog from "@/components/Vehicle/CreateTypeDialog";
import VehicleCategoryPicker from "@/components/Common/VehicleCategoryPicker.vue";

export default {
  name: "VehicleGroupView",
  components: {VehicleCategoryPicker, CreateTypeDialog, FileUploader},
  data() {
    return {
      group_id: Number(this.$route.params.id),
      group: {},
      editGroup: {},
      types: [],
      createDialog: false,
      errors: {},
    }
  },
  created() {
    this.getGroup();
    this.getTypes();
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Категории техники',
          disabled: false,
          href: '/admin/vehicles',
        },
        {
          text: `${this.group?.title}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getGroup() {
      this.$http.get(`vehicle-groups/${this.group_id}`).then(r => {
        this.group = r.body.vehicleGroup;
        this.editGroup = this.copyObject(this.group);
      })
    },
    getTypes() {
      this.$http.get(`vehicle-groups/${this.group_id}/vehicle-types`).then(r => {
        this.types = r.body.vehicleTypes;
      })
    },
    update() {
      this.errors = {};
      this.$http.put(`vehicle-groups/${this.group.id}`, this.editGroup).then(() => {
        this.group.title = this.editGroup.title;
        this.group.logo = this.editGroup.logo;
        Swal.fire('Изменения сохранены');
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    destroy() {
      Swal.fire({
        title: 'Вы действительно хотите удалить группу?',
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`vehicle-groups/${this.group.id}`).then(() => {
            this.$router.push(`/vehicles`);
          })
        }
      })
    },
    onTypeCreated(type) {
      this.types.push(type);
    }
  }
}
</script>

<style scoped>

</style>