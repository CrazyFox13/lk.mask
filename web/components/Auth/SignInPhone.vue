<template>
  <div>
    <!-- Шаг 1: Ввод телефона и капчи -->
    <template v-if="step === 0">
      <PhoneInput v-model="credentials.phone" placeholder="Введите телефон" :error="errors.phone" />
      <CaptchaInput v-model="credentials.captcha_token" :error="errors.captcha_token"/>
      <div class="flex justify-between">
        <el-checkbox label="Запомнить меня" class="text-sub-subtitle" v-model="credentials.remember" />
        <el-button link @click="phoneWay()" class="text-sub-subtitle flex-end">Войти по e-mail</el-button>
      </div>
      <el-button class="flex my-20" type="primary" @click="sendCode()" :loading="loading">Получить код</el-button>
    </template>

    <!-- Шаг 2: Ввод кода из SMS -->
    <template v-if="step === 1">
      <el-button circle plain @click="goBack" class="back-btn">
        <SvgIcon name="back"/>
      </el-button>
      <div class="text-h3 text-center">Введите код из СМС</div>
      <p class="text-center">Мы отправили код на номер <br/> {{ credentials.phone }}</p>
      <TextInput v-model="phoneCode" placeholder="Введите код" :error="errors.phone_code"/>
      <el-button class="flex mb-20" type="primary" @click="submit()" :loading="loading">Войти</el-button>
      <el-button link @click="resendCode()" :disabled="resendDisabled">
        {{ resendDisabled ? `Запросить код повторно через ${resendTimer} сек` : 'Запросить код повторно' }}
      </el-button>
      <p v-if="errors.phone" class="text-error text-sub-subtitle">{{ errors.phone }}</p>
    </template>
  </div>
</template>

<script setup lang="ts">

import PhoneInput from "~/components/Common/Forms/PhoneInput.vue";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import CaptchaInput from "~/components/Auth/CaptchaInput.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import { useAuthStore } from "~/stores/user";
import { type ISendPhoneCodeCredentials } from "~/stores/user";
import { navigateTo, onMounted, onUnmounted, ref, useCookie, watch } from "#imports";

const emit = defineEmits(['changeType']);

const step = ref(0);
const loading = ref(false);
const phoneCode = ref('');
const resendTimer = ref(60);
const resendDisabled = ref(false);
let timerInterval: ReturnType<typeof setInterval> | null = null;

const credentials = ref<ISendPhoneCodeCredentials & { remember: boolean }>({
  phone: '',
  captcha_token: '',
  remember: true
});
const interacted = ref(false);
const { sendPhoneCode, confirmPhoneCode, getUser } = useAuthStore();
const errors = ref<any>({})
const redirectCookie = useCookie('redirect_to');
const cookie = useCookie('auth-phone');

onMounted(() => {
  if (cookie.value) {
    try {
      const saved = JSON.parse(cookie.value as string);
      if (saved.phone) {
        credentials.value.phone = saved.phone;
        credentials.value.remember = saved.remember ?? true;
      }
    } catch (e) {
      // ignore
    }
  }
});

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
});

watch(credentials, () => {
  if (!interacted.value) {
    try {
      (window as any).ym(65983300, 'reachGoal', 'signin_start')
    } catch (e) {
      console.log("todo: Yandex Metrika", 'start login');
    }
    interacted.value = true;
    return;
  }
}, { deep: true })

const startResendTimer = () => {
  resendDisabled.value = true;
  resendTimer.value = 60;
  timerInterval = setInterval(() => {
    resendTimer.value--;
    if (resendTimer.value <= 0) {
      resendDisabled.value = false;
      if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
      }
    }
  }, 1000);
};

const sendCode = () => {
  errors.value = {};
  loading.value = true;
  sendPhoneCode({ phone: credentials.value.phone, captcha_token: credentials.value.captcha_token })
    .then(() => {
      step.value = 1;
      startResendTimer();
    })
    .catch(({ body }) => {
      errors.value = body.errors;
    })
    .finally(() => {
      loading.value = false;
    });
};

const resendCode = () => {
  errors.value = {};
  sendPhoneCode({ phone: credentials.value.phone, captcha_token: credentials.value.captcha_token })
    .then(() => {
      startResendTimer();
    })
    .catch(({ body }) => {
      errors.value = body.errors;
    });
};

const submit = () => {
  errors.value = {};
  loading.value = true;
  confirmPhoneCode({ phone: credentials.value.phone, phone_code: phoneCode.value })
    .then(() => {
      if (credentials.value.remember) {
        cookie.value = JSON.stringify({ phone: credentials.value.phone, remember: true });
      }
      getUser().then(() => {
        if (redirectCookie.value) {
          navigateTo(redirectCookie.value);
          redirectCookie.value = '';
        } else {
          navigateTo('/profile');
        }
      });
      try {
        console.log("todo: Yandex Metrika", 'login');
        (window as any).ym(65983300, 'reachGoal', 'signin_end');
      } catch (e) {
        console.log(e);
      }
    })
    .catch(({ body }) => {
      errors.value = body.errors;
    })
    .finally(() => {
      loading.value = false;
    });
};

const goBack = () => {
  step.value = 0;
  phoneCode.value = '';
  errors.value = {};
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }
};

const phoneWay = () => {
  emit('changeType', 'email');
};
</script>

<style scoped>
.el-button {
  width: 100%;
}

.my-20 {
  margin-bottom: 20px;
  margin-top: 20px;
}

.mb-20 {
  margin-bottom: 20px;
}

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