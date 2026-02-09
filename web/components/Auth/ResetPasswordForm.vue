<template>
  <div>
    <div class="text-h3 text-center">Восстановление пароля</div>
    <PhoneInput
        v-if="resetType==='phone'"
        v-model="phoneResetRequest.phone"
        placeholder="Введите телефон"
        :error="errors.phone"
    />
    <TextInput
        v-if="resetType==='email'"
        v-model="emailResetRequest.email"
        placeholder="Введите e-mail"
        :error="errors.email"
    />
    <div class="flex flex-end">
      <el-button class="flex-end" link @click="changeType()">
        Восстановить через
        {{ resetType === 'phone' ? 'e-mail' : 'телефон' }}
      </el-button>
    </div>
    <el-button class="flex my-20" type="primary" @click="submit()">Восстановить</el-button>
  </div>
</template>

<script setup lang="ts">
import PhoneInput from "../Common/Forms/PhoneInput.vue";
import {IEmailResetRequest, ISendPhoneCodeCredentials, useAuthStore} from "~/stores/user";
import TextInput from "~/components/Common/Forms/TextInput.vue";

const emit = defineEmits(['sent', 'email_sent']);
const {sendPhoneCode, resetPasswordByEmail} = useAuthStore();
const phoneResetRequest = ref<ISendPhoneCodeCredentials>({
  phone: ''
});

const emailResetRequest = ref<IEmailResetRequest>({
  email: ''
});

const resetType = ref<'phone' | 'email'>('phone');
const errors = ref<any>({})
const submit = () => {
  resetType.value === 'phone' ? phoneReset() : emailReset();
}

const phoneReset = () => {
  sendPhoneCode({...phoneResetRequest.value}).then(() => {
    emit('sent', phoneResetRequest.value.phone)
  }).catch(({body}) => {
    errors.value = body.errors;
  });
}

const emailReset = () => {
  resetPasswordByEmail({...emailResetRequest.value}).then(() => {
    emit('email_sent', emailResetRequest.value.email)
  }).catch(({body}) => {
    errors.value = body.errors;
  });
}

const changeType = () => {
  resetType.value = resetType.value === 'phone' ? 'email' : 'phone';
}
</script>

<style scoped>
.text-h3 {
  margin-top: 30px;
  margin-bottom: 20px;
}

.el-button {
  width: 100%;
}

.my-20 {
  margin-top: 20px;
  margin-bottom: 20px;
}
</style>