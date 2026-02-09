<template>
  <v-card>
    <v-card-title>Редактирование модератора</v-card-title>
    <v-card-text>
      <v-text-field
          label="Имя"
          v-model="user.name"
          :error-messages="errors.name"
          :error-count="1"
          :error="!!errors.name"
      />
      <v-text-field
          label="Фамилия"
          v-model="user.surname"
          :error-messages="errors.surname"
          :error-count="1"
          :error="!!errors.surname"
      />
      <v-text-field
          label="Телефон"
          v-model="user.phone"
          :error-messages="errors.phone"
          :error-count="1"
          :error="!!errors.phone"
      />
      <v-text-field
          label="Email"
          v-model="user.email"
          :error-messages="errors.email"
          :error-count="1"
          :error="!!errors.email"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import Swal from "sweetalert2-khonik";

export default {
  name: "ModeratorEditDialog",
  props: ['value'],
  data() {
    return {
      user: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.user.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`moderators`, this.user).then(r => {
        this.$emit("created", r.body.user);
        this.$emit("close");
        Swal.fire({
          title: 'Пароль от учётной записи',
          text: r.body.plainPassword,
        })
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`moderators/${this.user.id}`, this.user).then(r => {
        this.$emit("updated", r.body.user);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>