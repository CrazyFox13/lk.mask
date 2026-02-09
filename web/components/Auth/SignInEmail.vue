<template>
  <div>
    <TextInput v-model="credentials.email" placeholder="Введите e-mail" :error="errors.email"/>
    <PasswordInput v-model="credentials.password" placeholder="Введите пароль" :error="errors.password"/>
    <div class="flex justify-between">
      <el-checkbox label="Запомнить меня" class="text-sub-subtitle" v-model="credentials.remember"/>
      <el-button link @click="phoneWay()" class="text-sub-subtitle flex-end">Войти по телефону</el-button>
    </div>
    <el-button class="flex my-20" type="primary" @click="submit()">Войти</el-button>
  </div>
</template>

<script setup lang="ts">

import PasswordInput from "~/components/Common/Forms/PasswordInput.vue";
import {useAuthStore} from "~/stores/user";
import {type ISignInEmailCredentials} from "~/stores/user";
import {navigateTo, useCookie, watch, onMounted, ref} from "#imports";
import TextInput from "~/components/Common/Forms/TextInput.vue";

const emit = defineEmits(['changeType']);

const credentials = ref<ISignInEmailCredentials>({
  email: '',
  password: '',
  remember: true
});
const cookie = useCookie('auth-email');

onMounted(() => {
  if (cookie.value) {
    credentials.value = cookie.value as unknown as ISignInEmailCredentials;
  }
});

const interacted = ref(false);
const {signInEmail, getUser} = useAuthStore();
const errors = ref<any>({})
const redirectCookie = useCookie('redirect_to');

watch(credentials, () => {
  if (!interacted.value) {
    console.log("todo: Yandex Metrika", 'start login email');
    (window as any).ym(65983300, 'reachGoal', 'signin_start')
    interacted.value = true;
    return;
  }
}, {deep: true})


const submit = () => {
  signInEmail({...credentials.value}).then(() => {
    if (credentials.value.remember) {
      cookie.value = JSON.stringify(credentials.value)
    }
    getUser().then(() => {
      if (redirectCookie.value) {
        navigateTo(redirectCookie.value)
        redirectCookie.value = ''
      } else {
        navigateTo('/profile')
      }
    })
    try {
      (window as any).ym(65983300, 'reachGoal', 'signin_end')
    } catch (e) {
      console.log(e);
    }
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}

const phoneWay = () => {
  emit('changeType', 'phone')
}
</script>

<style scoped>
.el-button {
  width: 100%;
}

.my-20 {
  margin-bottom: 20px;
  margin-top: 20px;
}
</style>