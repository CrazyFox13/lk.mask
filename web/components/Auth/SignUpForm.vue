<template>
  <div>
    <div class="text-h3 text-center">Регистрация</div>
    <p class="text-center">Ваш номер телефона будет использован в качестве логина в ваш личный кабинет</p>
    <PhoneInput v-model="credentials.phone" placeholder="Введите телефон" :error="errors.phone" />
    <CaptchaInput v-model="credentials.captcha_token" :error="errors.captcha_token"/>
    <el-button class="flex" type="primary" @click="submit()">Продолжить</el-button>
    <p class="text-sub-subtitle text-gray text-center">
      Нажимая на кнопку, вы соглашаетесь с
      <a href="/soglas/politika" target="_blank" class="text-gray ">
        пользовательским соглашением
      </a>
    </p>
  </div>
</template>

<script setup lang="ts">
import PhoneInput from "../Common/Forms/PhoneInput.vue";
import CaptchaInput from "~/components/Auth/CaptchaInput.vue";

import { useAuthStore } from "~/stores/user";
import { type ISendPhoneCodeCredentials } from "~/stores/user";
import { ref, watch } from "#imports";

const emits = defineEmits(['sent']);

const credentials = ref<ISendPhoneCodeCredentials>({
  phone: '', captcha_token: '',
});
const interacted = ref(false)

const errors = ref<any>({})

const { signUp } = useAuthStore();

watch(credentials, () => {
  if (!interacted.value) {
    try {
      (window as any).ym(65983300, 'reachGoal', 'regist_start')
    } catch (e) {
      console.log("todo: Yandex Metrika", 'start reg');
    }
    interacted.value = true;
    return;
  }
}, { deep: true })

const submit = () => {
  errors.value = {};
  signUp({ ...credentials.value }).then(() => {
    emits('sent', credentials.value.phone);
    try {
      (window as any).ym(65983300, 'reachGoal', 'regist_end')
    } catch (e) {
      console.log("todo: Yandex Metrika", 'regist_end')
    }
  }).catch(({ body }) => {
    errors.value = body.errors;
  })
}
</script>

<style scoped lang="scss">
.text-h3 {
  margin-top: 30px;
  /*margin-bottom: 20px;*/
}

.el-button {
  //  margin-top: 20px;
  width: 100%;
}

.text-sub-subtitle {
  margin-bottom: 7px;
}
</style>