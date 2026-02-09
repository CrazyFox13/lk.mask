<template>
  <div class="section bg-gray">
    <div class="container">
      <el-row>
        <el-col :span="24" :md="12">
          <el-card>
            <AuthTabs tab="sign-up"/>
            <SignUpForm key="0" v-if="step===0" @sent="onCodeSent"/>
            <ConfirmPhone
                key="1"
                v-if="step===1"
                :phone="phone"
                @back="onBack"
                @auth="onAuth"
                button_label="Зарегистрироваться"
            />
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
import AuthTabs from "~/components/Auth/AuthTabs.vue";
import AuthBanner from '~/assets/images/astt_auth.png';
import SignUpForm from "~/components/Auth/SignUpForm.vue";
import ConfirmPhone from "~/components/Auth/ConfirmPhone.vue";
import {definePageMeta, navigateTo, useRoute, useRouter} from "#imports";
import {useAuthStore} from "~/stores/user";

const step = ref(0);
const phone = ref('');
const {getUser} = useAuthStore();

definePageMeta({
  middleware: 'auth-redirect'
});

const onCodeSent = (phoneNumber: string) => {
  phone.value = phoneNumber;
  step.value = 1;
}

const onBack = () => {
  step.value = 0;
}

const onAuth = () => {
  getUser().then(() => {
    navigateTo('/auth/set-password')
  })
}

</script>

<style scoped lang="scss">
.el-card {
  @media (min-width: 992px) {
    width: 310px;
    margin-left: auto;
    padding: 20px 40px 60px 40px;
    border-radius: 10px 0 0 10px;
  }
}

.el-image {
  width: 390px;
  height: 502px;
  border-radius: 0 10px 10px 0;
}
</style>