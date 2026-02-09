<template>
  <div class="section bg-gray">
    <div class="container">
      <el-row>
        <el-col :span="24" :md="12">
          <el-card>
            <AuthTabs tab="sign-in" />
            <div class="text-h3 text-center">Вход в личный кабинет</div>
            <SignInPhone v-if="signInType === 'phone'" @changeType="onChangeType" />
            <SignInEmail v-if="signInType === 'email'" @changeType="onChangeType" />
            <div class="flex flex-end" v-if="signInType === 'email'">
              <nuxt-link class="el-link text-sub-subtitle" to="/auth/reset-password">Забыли пароль?</nuxt-link>
            </div>
          </el-card>
        </el-col>
        <el-col :span="12" class="hidden-sm-and-down">
          <el-image :src="AuthBanner" alt="auth banner" />
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import AuthTabs from "../../components/Auth/AuthTabs.vue";
import AuthBanner from '~/assets/images/astt_auth.png';
import SignInPhone from "~/components/Auth/SignInPhone.vue";
import SignInEmail from "~/components/Auth/SignInEmail.vue";
import { definePageMeta } from "#imports";

const signInType = ref<'phone' | 'email'>('phone')

definePageMeta({
  middleware: 'auth-redirect'
});

const onChangeType = (type: 'phone' | 'email') => {
  signInType.value = type;
}

</script>

<style scoped lang="scss">
.el-card {

  .text-h3 {
    margin-top: 30px;
    margin-bottom: 20px;
  }

  @media (min-width: 992px) {
    width: 310px;
    margin-left: auto;
    padding: 20px 40px 20px 40px;
    border-radius: 10px 0 0 10px;
  }
}

.el-image {
  width: 390px;
  height: 502px;
  border-radius: 0 10px 10px 0;
}
</style>