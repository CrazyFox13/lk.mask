<template>
  <div class="section bg-gray">
    <div class="container">
      <el-row>
        <el-col :span="24" :md="12">
          <el-card shadow="never">
            <div class="text-h3 text-center">Создание нового пароля</div>
            <PasswordInput :error="errors.password" v-model="credentials.password" placeholder="Введите пароль"/>
            <PasswordInput v-model="credentials.password_confirmation" placeholder="Подтвердите пароль"/>
            <el-button class="flex" type="primary" @click="submit()">Подтвердить</el-button>
          </el-card>
        </el-col>
        <el-col :span="12" class="hidden-sm-and-down">
          <el-image :src="AuthBanner" alt="auth banner"/>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import AuthBanner from '~/assets/images/auth_banner.png';
import PasswordInput from "~/components/Common/Forms/PasswordInput.vue";
import {useAuthStore} from "~/stores/user";
import type {ISetPasswordRequest} from "~/stores/user";
import {definePageMeta, navigateTo, ref, useCookie} from "#imports";

definePageMeta({
  middleware: ["auth"]
});

const credentials = ref<ISetPasswordRequest>({
  password: '',
  password_confirmation: ''
})

const errors = ref<any>({})
const redirectCookie = useCookie('redirect_to');

const submit = () => {
  const {setPassword, getUser} = useAuthStore();
  setPassword({...credentials.value}).then(() => {
    getUser().then(() => {
      if (redirectCookie.value) {
        navigateTo(redirectCookie.value);
        redirectCookie.value = ''
      } else {
        navigateTo('/profile')
      }
    })
  }).catch(({body}) => {
    errors.value = body.errors;
  });
}
</script>

<style scoped lang="scss">
.el-card {
  .text-h3 {
    margin-bottom: 20px;
  }

  @media (min-width: 992px) {
    width: 310px;
    margin-left: auto;
    padding: 40px;
    border-radius: 10px 0 0 10px;
    height: 300px;

    .el-button {
      width: 100%;
    }
  }
}

.el-image {
  width: 390px;
  height: 390px;
  border-radius: 0 10px 10px 0;
}
</style>