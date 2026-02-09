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
          <div class="text-black modal-window-title text-center mb-10">Оставьте рекомендацию</div>
          <p class="text-gray text-center mt-0">При написании негативного текста ваш рейтинг <br/> будет снижен, а отзыв
            будет удален</p>
        </div>
      </template>


      <div class="mb-20" v-bind:class="{'input-error':!!errors.text}">
        <div class="text-h6 mb-10">Содержание рекомендации (минимум 15 символов)*</div>
        <el-input
            autosize
            type="textarea"
            placeholder="Введите текст"
            v-model="recommendation.text"
            min="15"
        />
        <p v-if="!!errors.text">{{ errors.text }}</p>
      </div>
      <div class="text-center">
        <el-button
            :disabled="!isFormReady"
            class="btn-submit"
            :type="isFormReady?'primary':'info'"
            @click="submit()"
        >
          Отправить рекомендацию
        </el-button>
      </div>
    </el-dialog>
  </client-only>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, computed, useRouter} from "#imports";

interface IRecommendation {
  text: string
  company_id: number
  target_user_id: number
}

const props = defineProps(['company']);
const emit = defineEmits(['close']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);
const router = useRouter();
const recommendation = ref<IRecommendation>({
  text: "",
  company_id: props.company.id,
  target_user_id: props.company.boss.id,
});
const errors = ref<any>({})

const isFormReady = computed(() => {
  return recommendation.value.text.length > 15;
})

const submit = () => {
  errors.value = {};
  apiFetch(`recommendations`, {
    method: "post",
    body: recommendation.value
  }).then(() => {
    router.push('/recommendations/created');
  }).catch(({body}) => {
    errors.value = body.errors;
  });
}

const handleClose = () => {
  dialog.value = false;
  emit('close')
}
</script>

<style scoped lang="scss">
.btn-submit {
  width: 100%;
  max-width: initial;

  @media (min-width: 992px) {
    width: auto;
  }
}
</style>