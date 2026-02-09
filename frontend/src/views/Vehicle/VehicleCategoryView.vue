<template>
  <div>
    <v-breadcrumbs :items="breadcrumps" divider="-"/>
    <div class="text-h6 d-flex align-center">
      <div class="ml-1">{{ category.title }}</div>
    </div>

    <v-row>
      <v-col cols="12" md="6">
        <div class="d-flex justify-space-between align-center mt-6 mb-4">
          <div class="text-subtitle-1">Изменить категорию</div>
        </div>
        <v-text-field
            label="Название"
            v-model="editCategory.title"
            :error="!!errors.title"
            :error-count="1"
            :error-messages="errors.title"
        />

        <v-switch
            label="Показывать в меню"
            v-model="editCategory.show_in_menu"
            :error="!!errors.show_in_menu"
            :error-count="1"
            :error-messages="errors.show_in_menu"
        />


        <div v-if="editCategory.image">
          <v-img :src="editCategory.image" width="120" contain />
        </div>
        <file-uploader
            v-if="editCategory.show_in_menu"
            label="Изображение для меню"
            v-model="editCategory.image"
            :error="errors.image"
        />

        <div v-if="editCategory.show_in_menu">
          <label class="text-gray">Цвет фона в меню</label>
          <v-color-picker
              :value="editCategory.color || ''"
              @update:color="(v)=>editCategory.color = v.hexa"
          />
        </div>

        <div class="d-flex justify-space-between align-center mt-2">
          <v-btn small color="error" @click="destroy()">Удалить</v-btn>
          <v-btn color="primary" @click="update()">Изменить</v-btn>
        </div>
      </v-col>
      <v-col cols="12" md="6">
        <div class="d-flex justify-space-between align-center mt-6 mb-4">
          <div class="text-subtitle-1">Наименование групп</div>
        </div>
        <v-alert v-if="groups.length===0" type="info" outlined>Не выбрана для групп</v-alert>
        <v-list v-else>
          <v-list-item v-for="group in groups" :key="group.id">
            <v-list-item-content>
              <v-list-item-title>{{ group.title }}</v-list-item-title>
            </v-list-item-content>
            <v-list-item-action>
              <v-btn icon :to="`/vehicle-groups/${group.id}`">
                <v-icon>mdi-eye</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </v-list>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import FileUploader from "@/components/Common/FileUploader";
import Swal from "sweetalert2-khonik";

export default {
  name: "VehicleGroupView",
  components: {FileUploader},
  data() {
    return {
      category_id: Number(this.$route.params.id),
      category: {},
      editCategory: {},
      groups: [],
      errors: {},
    }
  },
  created() {
    this.getCategory();
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Категории техники',
          disabled: false,
          href: '/admin/vehicle-categories',
        },
        {
          text: `${this.category?.title}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getCategory() {
      this.$http.get(`vehicle-categories/${this.category_id}`).then(r => {
        this.category = r.body.vehicleCategory;
        this.groups = this.category.groups;
        this.editCategory = this.copyObject(this.category);
      })
    },
    update() {
      this.errors = {};
      this.$http.put(`vehicle-categories/${this.category.id}`, this.editCategory).then(() => {
        this.category.title = this.editCategory.title;
        Swal.fire('Изменения сохранены');
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    destroy() {
      Swal.fire({
        title: 'Вы действительно хотите удалить категорию?',
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`vehicle-categories/${this.category.id}`).then(() => {
            this.$router.push(`/vehicle-categories`);
          })
        }
      })
    },
  }
}
</script>

<style scoped>

</style>