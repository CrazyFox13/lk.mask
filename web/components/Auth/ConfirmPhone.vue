<template>
  <div>
    <el-button circle plain @click="goBack" class="back-btn">
      <SvgIcon name="back"/>
    </el-button>
    <div class="text-h3 text-center">Введите код из СМС</div>
    <p class="text-center">Мы отправили код на номер <br/> {{ phone }}</p>
    <TextInput v-model="credentials.phone_code" placeholder="Введите код" :error="errors.phone_code"/>
    <el-button class="flex mb-20" type="primary" @click="submit()">{{ button_label }}</el-button>
    <!--<p class="text-sub-subtitle text-gray">
      Запросить код повторно можно через ...
    </p>-->
    <el-button link @click="resendCode()">Запросить код повторно</el-button>
    <p v-if="errors.phone" class="text-error text-sub-subtitle">{{ errors.phone }}</p>
  </div>
</template>

<script setup lang="ts">
import TextInput from "../Common/Forms/TextInput.vue";
import {useAuthStore} from "~/stores/user";
import {type IConfirmPhoneCredentials} from "~/stores/user";
import SvgIcon from "~/components/SvgIcon.vue";
import {ref} from "#imports";

const props = defineProps(['phone', 'reset_password', 'button_label']);
const emits = defineEmits(['auth', 'back']);

const credentials = ref<IConfirmPhoneCredentials>({
  phone: props.phone,
  phone_code: ''
});

const errors = ref<any>({})

const {confirmPhoneCode, sendPhoneCode} = useAuthStore();

const submit = () => {
  errors.value = {};
  confirmPhoneCode(credentials.value, {
    reset_password: !!props.reset_password
  }).then(({access_token}) => {
    emits('auth', true)
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}

const resendCode = () => {
  sendPhoneCode({...credentials.value}).then(() => {
    console.log("Code sent")
  }).catch(({body}) => {
    errors.value = body.errors;
    console.log(errors.value);
  })
}

const goBack = () => {
  emits('back', true);
}
</script>

<style scoped lang="scss">
.text-h3 {
  margin-top: 30px;
  /* margin-bottom: 20px;*/
}

.el-button {
  width: 100%;
}

.mb-20 {
  margin-bottom: 20px;
}

.text-error {
  margin-top: 5px;
}

.back-btn {
  position: absolute;
  width: 24px;
  height: 24px;
  border: none;
}
</style>