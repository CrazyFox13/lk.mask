<template>
  <AlarmDialog drawer-height="315">
    <template #header>
      <div class="dialog-head">
        <el-button class="close-btn text-black hidden-md-and-up" circle text @click="emit('close')">
          <SvgIcon name="close"/>
        </el-button>
        <el-button class="close-btn text-black hidden-sm-and-down" circle text @click="emit('close')">
          <SvgIcon name="close"/>
        </el-button>
      </div>
    </template>
    <template #body>
      <div class="text-center on-moderation">
        <div class="text-black modal-form-title title-margin text-center">Сохранить поиск</div>
        <TextInput
            placeholder="Введите название"
            v-model="orderFilter.name"
            :error="errors.name"
        />
        <!--<div class="flex justify-between mt-15">
          <label>
            Push-уведомления
          </label>
          <el-switch v-model="orderFilter.active_push"/>
        </div>-->
        <div class="flex justify-between mt-15">
          <label>
            Получать E-mail уведомления
          </label>
          <el-switch v-model="orderFilter.active_email"/>
        </div>
        <el-button class="submit-margin" type="primary" @click="saveFilter()">Сохранить</el-button>
      </div>
    </template>
  </AlarmDialog>
</template>

<script setup lang="ts">
import AlarmDialog from "~/components/Common/AlarmDialog.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import {apiFetch} from "~/composables/apiFetch";
import {useRouter, watch} from "#imports";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

const emit = defineEmits(['close']);
const props = defineProps(['searchName', 'filterQuery']);
const {user} = storeToRefs(useAuthStore());
if(!user.value){
  const router = useRouter();
  router.push("/auth/sign-in");
}

const errors = ref({})
const orderFilter = ref({
  name: props.searchName,
  active_push: false,
  active_email: false,
  query: props.filterQuery
});

watch(props, () => {
  orderFilter.value.name = props.searchName;
  orderFilter.value.query = props.filterQuery;
}, {deep: true})

const saveFilter = () => {
  errors.value = {};
  apiFetch(`order-filters`, {
    method: "POST",
    body: orderFilter.value
  }).then(() => {
    emit('close');
  }).catch(({body}) => {
    errors.value = body.errors;
  });
}
</script>

<style scoped lang="scss">

.submit-margin {
  margin-top: 20px;
  width: 100%;
  @media (min-width: 992px) {
    margin-top: 30px;
    width: 200px;
  }
}

.title-margin {
  margin-top: 0;
  margin-bottom: 10px;
  @media (min-width: 992px) {
    margin-bottom: 30px;
  }
}
</style>