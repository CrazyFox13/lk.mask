<template>
  <v-card max-width="400" class="mx-auto mt-5">
    <v-img max-width="400" :src="require('@/assets/images/track_pic.png')" />
    <v-card-text>Вход в панель администратора</v-card-text>
    <v-card-text>
      <v-text-field label="Email" v-model="credentials.email" :error-messages="errors.email" :error-count="1"
        :error="!!errors.email" />
      <v-text-field type="password" label="Пароль" v-model="credentials.password" :error-messages="errors.password"
        :error-count="1" :error="!!errors.password" />
      <div class="mb-10">
        <div style="height: 100px" id="captcha-container" class="smart-captcha"></div>
        <p class="text-sub-subtitle text-error" v-if="!!errors.captcha_token">{{ errors.captcha_token }}</p>
      </div>
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="login()">Войти</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "LoginView",
  data() {
    return {
      credentials: {},
      errors: {},

      captcha_key: "ysc1_1VuBx5qmmijsrjugUeElBYOu7LR1jToqzlu3yUVS25d29a99",
    }
  },
  mounted() {
    this.$nextTick(() => {
      const w = window
      if (w.smartCaptcha) {
        const container = document.getElementById('captcha-container');
        const widgetId = w.smartCaptcha.render(container,{
          sitekey: this.captcha_key
        });
      
        w.smartCaptcha.subscribe(
          widgetId,
          'success',
          (token) => this.credentials.captcha_token = token,
        );
      }
    })
  },
  methods: {
    login() {
      this.errors = {};
      this.$http.post(`auth/login/password`, this.credentials).then(r => {
        this.$store.commit('auth', r.body.access_token);
        this.$store.dispatch('GET_ME').then(() => {
          this.$router.push('/')
        })
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped></style>