<template>
  <el-card>
    <div class="text-h3 mb-30">Изменить пароль</div>
    <div class="input-group">
      <label class="input-title">Введите старый пароль*</label>
      <PasswordInput
          placeholder="Введите старый пароль" v-model="value.old_password"
          :error="errors.old_password"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Введите новый пароль*</label>
      <PasswordInput
          placeholder="Введите новый пароль" v-model="value.password"
          :error="errors.password"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Повторите пароль*</label>
      <PasswordInput
          placeholder="Введите пароль" v-model="value.password_confirmation"
          :error="errors.password_confirmation"
      />
    </div>
    <el-button
        class="btn-submit"
        :disabled="!isFormReady"
        :type="isFormReady?'primary':'info'"
        @click="submit()"
    >
      Сохранить пароль
    </el-button>
  </el-card>
</template>

<script setup lang="ts">
import PasswordInput from "~/components/Common/Forms/PasswordInput.vue";
import { useAuthStore} from "~/stores/user";
import {type IUpdatePasswordRequest} from "~/stores/user";
import {computed, ref} from "#imports";

const value = ref<IUpdatePasswordRequest>({
  old_password: '',
  password: '',
  password_confirmation: '',
});
const {setPassword} = useAuthStore();
const errors = ref<any>({})
const isFormReady = computed(() => {
  return value.value.old_password && value.value.password && value.value.password_confirmation;
});
const submit = () => {
  errors.value = {};
  setPassword(value.value).then(() => {

  }).catch(({body}) => {
    errors.value = body.errors;
  })
}
</script>

<style scoped lang="scss">

</style>