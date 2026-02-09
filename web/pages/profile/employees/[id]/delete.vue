<template>
  <el-card>
    <div class="flex align-items-center title">
      <nuxt-link class="el-button el-button--info link-circle mr-5" :to="`/profile/employees/${value.id}`">
        <SvgIcon name="back"/>
      </nuxt-link>
      <div class="text-h3">Удалить сотрудника</div>
    </div>
    <el-row>
      <el-col :span="24" :md="12">
        <div>
          <label class="input-title">
            Выберите сотрудника, который будет ответственным по всем заявкам, отзывам вместо удаляемого*
          </label>
          <SelectInput
              v-model="value.user_id"
              placeholder="Не выбрано"
              :error="errors.user_id"
              :options="usersToSelect"
          />
        </div>
        <div class="mt-10">
          <label class="input-title">Пароль*</label>
          <TextInput
              type="password"
              v-model="value.password"
              placeholder="Введите пароль"
              :error="errors.password"
          />
        </div>

        <div class="buttons">
          <el-button
              :disabled="!isFormReady"
              :type="isFormReady?'primary':'info'"
              @click="submit()"
          >Удалить
          </el-button>
          <nuxt-link
              class="el-button el-button--primary  is-plain"
              type="primary"
              :to="`/profile/employees/${value.id}`"
          >Отменить
          </nuxt-link>
        </div>
      </el-col>
    </el-row>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import {apiFetch, computed, navigateTo, useRoute, useRouter} from "#imports";
import {useAuthStore} from "~/stores/user";
import SelectInput from "~/components/Common/Forms/SelectInput.vue";

const {user} = useAuthStore();
const route = useRoute();

// Только владелец компании может удалять сотрудников
if (user?.company_role !== 'boss') {
  navigateTo('/profile/employees');
}

const data = await apiFetch(`companies/${user!.company_id}/employees/${route.params.id}`);
const {users} = await apiFetch(`companies/${user!.company_id}/employees`);
const value = ref({
  user_id: null,
  password: undefined
});
const errors = ref<any>({});

const isFormReady = computed(() => {
  return !!value.value.password;
});

const usersToSelect = computed(() => {
  return [{
    label: 'На администратора',
    value: null
  }, ...users.filter((u: any) => u.id !== Number(route.params.id)).map((u: any) => {
    return {
      label: [u.name, u.surname].join(" "),
      value: u.id,
    }
  })];
})

const router = useRouter();
const submit = () => {
  errors.value = {};
  apiFetch(`companies/${user!.company_id}/employees/${route.params.id}`, {
    method: "DELETE",
    body: {
      user_id: value.value.user_id,
      password: value.value.password
    }
  }).then(() => {
    router.push(`/profile/employees`)
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

label {
  display: block;
  margin-bottom: 10px;
}

.buttons {
  display: flex;
  flex-flow: column;
  row-gap: 20px;
  margin-top: 20px;

  button, a {
    width: 100%;
    margin: 0 !important;
  }

  @media (min-width: 992px) {
    flex-flow: row;
    column-gap: 20px;

    button, a {
      max-width: 180px;
    }
  }
}
</style>