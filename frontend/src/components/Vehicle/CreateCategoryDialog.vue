<template>
  <v-card>
    <v-card-title>Создание категории техники</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название категория"
          v-model="category.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />
      <v-switch
          label="Показывать в меню"
          v-model="category.show_in_menu"
          :error="!!errors.show_in_menu"
          :error-count="1"
          :error-messages="errors.show_in_menu"
      />


      <file-uploader
          v-if="category.show_in_menu"
          label="Изображение для меню"
          v-model="category.image"
          :error="errors.image"
      />

      <div v-if="category.show_in_menu">
        <label class="text-gray">Цвет фона в меню</label>
        <v-color-picker
            v-model="category.color"
            mode="hexa"
        />
      </div>
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="create()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import FileUploader from "@/components/Common/FileUploader";
export default {
  name: "CreateDialog",
  components: {FileUploader},
  data() {
    return {
      category: {
        color: "#ffd5d5"
      },
      errors: {},
    }
  },
  methods: {
    create() {
      this.errors = {};
      this.$http.post(`vehicle-categories`, this.category).then(r => {
        this.$emit('created', r.body.vehicleCategory);
        this.$emit('close');
        this.category={};
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>