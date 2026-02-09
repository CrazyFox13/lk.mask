<template>
  <el-card>
    <div class="flex align-items-center title">
      <nuxt-link class="el-button el-button--info link-circle mr-5" to="/profile/employees">
        <SvgIcon name="back"/>
      </nuxt-link>
      <div class="text-h3">Новый сотрудник</div>
    </div>
    <div class="input-group">
      <label class="input-title">Имя*</label>
      <TextInput
          v-model="value.name"
          placeholder="Введите имя"
          :error="errors.name"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Фамилия*</label>
      <TextInput
          v-model="value.surname"
          placeholder="Введите фамилию"
          :error="errors.surname"
      />
    </div>
    <div class="input-group">
      <label class="input-title">Телефон*</label>
      <TextInput
          v-model="value.phone"
          placeholder="+7 (999) 999-99-99"
          :error="errors.phone"
          mask="+7 (###) ###-##-##"
      />
    </div>
    <div class="input-group">
      <label class="input-title">E-mail*</label>
      <TextInput
          v-model="value.email"
          placeholder="mail@yahoo.ru"
          :error="errors.email"
      />
    </div>
    <el-button
        class="submit"
        :disabled="!isFormReady"
        :type="isFormReady?'primary':'info'"
        @click="submit()"
    >Добавить сотрудника
    </el-button>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import {apiFetch, computed, navigateTo, useRouter} from "#imports";
import {useAuthStore} from "~/stores/user";

const {user} = useAuthStore();

// Только владелец компании может добавлять сотрудников
if (user?.company_role !== 'boss') {
  navigateTo('/profile/employees');
}

const value = ref({
  name: "",
  surname: "",
  email: "",
  phone: ""
});
const errors = ref<any>({});

const isFormReady = computed(() => {
  return value.value.name && value.value.surname && value.value.email && value.value.phone;
});

const router = useRouter();
const submit = () => {
  errors.value = {};
  apiFetch(`companies/${user!.company_id}/employees`, {
    method: "POST",
    body: value.value
  }).then(() => {
    router.push(`/profile/employees/created`)
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}
</script>

<style scoped lang="scss">

.title {
  margin-bottom: 20px;
  @media (min-width: 992px) {
    margin-bottom: 30px;
  }
}

.submit {
  width: 100%;

  @media (min-width: 992px) {
    max-width: 250px;
  }
}

</style>