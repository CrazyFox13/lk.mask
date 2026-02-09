<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12" class="d-flex justify-space-between align-center">
      <div class="text-h6">Профиль</div>
    </v-col>
    <v-col cols="12" md="3">
      <v-img :src="user.avatar" max-width="150" class="mx-auto"/>
      <file-uploader
          @input="setAvatar"
          v-model="user.avatar"
          label="Изменить аватар"
      />
    </v-col>
    <v-col cols="12" md="6">
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
      <div class="d-flex">
        <v-btn color="primary" class="mt-3" @click="save()">Сохранить</v-btn>
        <v-spacer/>
        <v-btn text class="mt-3" @click="passwordDialog=true">Изменить пароль</v-btn>
      </div>
    </v-col>

    <v-dialog v-model="passwordDialog" max-width="400">
      <v-card>
        <v-card-title>Изменить пароль</v-card-title>
        <v-card-text>
          <v-text-field
              label="Старый пароль"
              type="password"
              outlined
              dense
              v-model="credentials.old_password"
              :error-messages="errors.old_password"
              :error-count="1"
              :error="!!errors.old_password"
          />
          <v-text-field
              label="Новый пароль"
              type="password"
              outlined
              dense
              v-model="credentials.password"
              :error-messages="errors.password"
              :error-count="1"
              :error="!!errors.password"
          />
          <v-text-field
              label="Подтвердите пароль"
              type="password"
              outlined
              dense
              v-model="credentials.password_confirmation"
              :error-messages="errors.password_confirmation"
              :error-count="1"
              :error="!!errors.password_confirmation"
          />
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="changePassword()">Сохранить</v-btn>
          <v-spacer/>
          <v-btn text @click="passwordDialog=false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import Swal from 'sweetalert2-khonik'
import FileUploader from "@/components/Common/FileUploader";

export default {
  name: "ProfileView",
  components: {FileUploader},
  data() {
    return {
      user: {},
      errors: {},
      passwordDialog: false,
      credentials: {}
    }
  },
  created() {
    this.user = this.$store.state.user;

    this.uns = this.$store.subscribe((mutation, state)=>{
      if (mutation.type==='setUser'){
        this.user=state.user;
      }
    })
  },
  beforeDestroy() {
    this.uns()
  },
  /*watch: {
    'user.avatar': function () {
      this.setAvatar()
    }
  },*/
  methods: {
    setAvatar() {
      this.$http.post(`auth/avatar`, {
        avatar: this.user.avatar
      }).then(() => {
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    save() {
      this.$http.post(`auth/profile`, this.user).then(r => {
        this.$store.commit("setUser", r.body.user);
        Swal.fire("Данные сохранены")
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    changePassword() {
      this.errors = {};
      this.$http.post(`auth/change-password`, this.credentials).then(() => {
        Swal.fire('Пароль успешно изменён');
        this.passwordDialog = false;
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>
