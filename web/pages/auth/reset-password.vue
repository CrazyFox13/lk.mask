<template>
  <div class="section bg-gray">
    <div class="container">
      <el-row>
        <el-col :span="24" :md="12">
          <el-card>
            <AuthTabs tab="sign-in"/>
            <ResetPasswordForm key="0" v-if="step===0" @sent="onCodeSent" @email_sent="onEmailSent"/>
            <ConfirmPhone
                reset_password="1"
                key="1"
                v-if="step===1"
                :phone="phone"
                @back="onBack"
                @auth="onAuth"
                button_label="Восстановить"
            />
            <ResetEmailSent
                v-if="step===2"
                key="2"
                @back="onBack"
                :email="email"
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
import AuthBanner from '~/assets/images/auth_banner.png';
import ResetPasswordForm from "~/components/Auth/ResetPasswordForm.vue";
import ConfirmPhone from "~/components/Auth/ConfirmPhone.vue";
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";
import {navigateTo} from "#imports";
import AuthTabs from "~/components/Auth/AuthTabs.vue";
import ResetEmailSent from "~/components/Auth/ResetEmailSent.vue";

const step = ref(0);
const phone = ref('');
const email = ref('');

const onCodeSent = (phoneNumber: string) => {
  phone.value = phoneNumber;
  step.value = 1;
}

const onEmailSent = (emailValue: string) => {
  email.value = emailValue;
  step.value = 2;
}

const onBack = () => {
  step.value = 0;
}

const onAuth = async () => {
  const {getUser} = useAuthStore();
  await getUser()
  const {user} = storeToRefs(useAuthStore());
  if (user.value) {
    navigateTo('/auth/set-password');
  } else {
    onBack();
  }
}
</script>

<style scoped lang="scss">
.el-card {
  @media (min-width: 992px) {
    width: 310px;
    margin-left: auto;
    padding: 40px;
    border-radius: 10px 0 0 10px;
    height: 310px;
  }
}

.el-image {
  width: 390px;
  height: 390px;
  border-radius: 0 10px 10px 0;
}
</style>