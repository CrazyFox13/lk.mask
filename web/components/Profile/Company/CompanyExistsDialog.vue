<template>
  <client-only>
    <el-dialog
        v-model="dialog"
        :fullscreen="isMobile"
        width="570"
        :before-close="handleClose"
        :show-close="false"
    >
      <template #header="{close}">
        <div class="dialog-head">
          <el-button class="close-btn text-black" circle text @click="close">
            <SvgIcon name="close"/>
          </el-button>
          <div class="text-black modal-window-title text-center mt-0 mb-10">Компания уже существует</div>
        </div>
      </template>

      <p class="text-center text-gray mt-0" style="word-break: normal;">
        Свяжитесь с тех поддержкой, если у вас нет доступа. Мы поможем разобраться в ситуации.
      </p>
      <div class="mb-20" v-bind:class="{'input-error':!!errors.name}">
        <div class="text-h6 mb-10">Ваше имя*</div>
        <TextInput
            placeholder="Введите имя"
            v-model="request.name"
        />
        <p v-if="!!errors.name">{{ errors.name }}</p>
      </div>

      <div class="mb-20" v-bind:class="{'input-error':!!errors.phone}">
        <div class="text-h6 mb-10">Ваш телефон*</div>
        <TextInput
            placeholder="+7 (999) 999-99-99"
            mask="+7 (###) ###-##-##"
            v-model="request.phone"
        />
        <p v-if="!!errors.phone">{{ errors.phone }}</p>
      </div>

      <div class="mb-20" v-bind:class="{'input-error':!!errors.email}">
        <div class="text-h6 mb-10">Ваш e-mail*</div>
        <TextInput
            placeholder="mail@yahoo.ru"
            v-model="request.email"
        />
        <p v-if="!!errors.email">{{ errors.email }}</p>
      </div>

      <div class="mb-20" v-bind:class="{'input-error':!!errors.text}">
        <div class="text-h6 mb-10">Опишите вашу проблему</div>
        <TextInput
            autosize
            type="textarea"
            placeholder="Введите текст"
            v-model="request.text"
            min="15"
        />
        <p v-if="!!errors.text">{{ errors.text }}</p>
      </div>


      <div class="text-center">
        <el-button
            :disabled="!isFormReady"
            class="btn-submit mt-20"
            :type="isFormReady?'primary':'info'"
            @click="submit()"
        >
          Отправить
        </el-button>
      </div>
    </el-dialog>
  </client-only>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, computed} from "#imports";
import TextInput from "~/components/Common/Forms/TextInput.vue";

const emit = defineEmits(['close','sent']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);
const errors = ref({});
const request = ref({
  name: "",
  email: "",
  phone: "",
  text: ""
});

const isFormReady = computed(() => {
  return request.value.name && request.value.email && request.value.phone && request.value.text;
});

const handleClose = () => {
  dialog.value = false;
  emit('close')
}

const submit = () => {
  errors.value = {};
  apiFetch(`support`, {
    method: "POST",
    body: request.value
  }).then(()=>{
    emit('sent')
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}
</script>

<style scoped lang="scss">
.btn-submit {
  width: 100%;

  @media (min-width: 992px) {
    width: auto;
  }
}
</style>