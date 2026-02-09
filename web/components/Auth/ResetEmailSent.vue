<template>
  <div>
    <el-button circle plain @click="goBack" class="back-btn">
      <SvgIcon name="back"/>
    </el-button>
    <div class="text-h3 text-center">Письмо отправлено</div>
    <p class="text-center">Мы отправили инструкцию на почту <br/> {{ email }}</p>
    <div class="flex justify-center">
      <el-button class="text-subtitle" link @click="resendCode()">Запросить письмо повторно</el-button>
    </div>
    <p v-if="errors.email" class="text-error text-sub-subtitle text-center">{{ errors.email }}</p>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "../SvgIcon.vue";
import {useAuthStore} from "~/stores/user";
import {ref} from "#imports";


const props = defineProps(['email']);
const emits = defineEmits(['back']);


const errors = ref<any>({})

const {resetPasswordByEmail} = useAuthStore();

const resendCode = () => {
  errors.value={};
  resetPasswordByEmail({
    email: props.email,
  }).then(() => {
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

<style scoped>

.text-h3 {
  margin-top: 30px;
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