<template>
  <el-card>
    <div class="text-h3 mb-30">Персональные данные</div>
    <div class="input-group">
      <label class="input-title">Имя*</label>
      <TextInput
          placeholder="Введите ваше имя"
          v-model="user.name"
          :error="errors.name"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Фамилия</label>
      <TextInput
          placeholder="Введите вашу фамилию"
          v-model="user.surname"
          :error="errors.surname"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Город</label>
      <CityInput
          placeholder="Не выбрано"
          v-model="user.geo_city_id"
          :error="errors.geo_city_id"
          :default="user.city"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Телефон</label>
      <PhoneInput
          placeholder="Введите ваш телефон"
          v-model="user.phone"
          :error="errors.phone"
      />
    </div>
    <div class="input-group">
      <label class="input-title">E-mail*</label>
      <div>
        <TextInput
            placeholder="Введите ваш e-mail" v-model="user.email"
            :error="errors.email"
        />
        <el-link @click="onSendVerificationLink"
                 type="primary"
                 class="text-sub-subtitle"
                 v-if="!user.email_verified_at"
        >Подтвердить
        </el-link>
      </div>
    </div>
    <el-button
        :disabled="!isFormReady"
        class="btn-submit"
        :type="isFormReady?'primary':'info'"
        @click="submit()"
    >
      Сохранить данные
    </el-button>
  </el-card>
</template>

<script setup lang="ts">
import TextInput from "~/components/Common/Forms/TextInput.vue";
import PhoneInput from "~/components/Common/Forms/PhoneInput.vue";
import {computed, ref} from "#imports";
import CityInput from "~/components/Common/Forms/CityInput.vue";
import {useAuthStore} from "~/stores/user";
import {type IUser} from "~/stores/user";
import {storeToRefs} from "pinia";

const {user} = storeToRefs(useAuthStore());
const errors = ref<any>({});
const {setProfile, sendEmailConfirmation} = useAuthStore();
const isFormReady = computed(() => {
  const data = user.value;
  return data && data.email && data.phone && data.name && data.surname;
});
const submit = () => {
  errors.value = {};
  setProfile(<IUser>user.value).then(() => {

  }).catch(({body}) => {
    errors.value = body.errors;
  })
}


const onSendVerificationLink = () => {
  if (!user.value) {
    alert("No user")
    return;
  }
  sendEmailConfirmation(user.value.email).then(() => {
    alert("Письмо с подтверждением отправлено на почту")
  }).catch(err => {
    alert(err.body.errors.email)
  })
}

</script>

<style scoped lang="scss">

</style>