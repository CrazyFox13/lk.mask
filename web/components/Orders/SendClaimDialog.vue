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
          <div class="text-black modal-window-title text-center mb-10">Жалоба на заявку</div>
        </div>
      </template>

      <div class="mb-20" v-bind:class="{'input-error':!!errors.text}">
        <div class="text-h6 mb-10">Опишите причину жалобы (минимум 15 символов)*</div>
        <el-input
            autosize
            type="textarea"
            placeholder="Введите текст"
            v-model="claim.text"
            min="15"
        />
        <p v-if="!!errors.text">{{ errors.text }}</p>
      </div>

      <div>
        <div class="text-h6">Файлы к жалобе:</div>
        <div class="mb-20">
          Вы можете прикрепить файлы, подтверждающие жалобу
        </div>
        <div class="flex flex-wrap">
          <DocumentItem
              v-for="(document,i) in claim.documents"
              :key="i"
              :document="document"
              :edit="true"
              @deleted="claim.documents.splice(i,1)"
          />
        </div>
        <FileUploader
            :multiple="true"
            @file_data="fileUploaded"
        />
      </div>
      <div class="text-center">
        <el-button
            :disabled="!isClaimReady"
            class="btn-submit mt-20"
            :type="isClaimReady?'primary':'info'"
            @click="submit()"
        >
          Отправить жалобу
        </el-button>
      </div>
    </el-dialog>
  </client-only>
</template>

<script lang="ts" setup>
import SvgIcon from "../SvgIcon.vue";
import FileUploader from "../Common/Forms/FileUploader.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, computed, useRouter} from "#imports";
import DocumentItem from "~/components/Common/DocumentItem.vue";

interface IClaim {
  text: string
  documents: any[]
  order_id: number
}

const props = defineProps(['order']);
const emit = defineEmits(['close']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);
const router = useRouter();
const claim = ref<IClaim>({
  text: "",
  documents: [],
  order_id: props.order.id,
});
const errors = ref<any>({})

const fileUploaded = (file: any) => {
  claim.value.documents.push(file);
}

const isClaimReady = computed(() => {
  return claim.value.text.length > 15;
})

const submit = () => {
  errors.value={};
  apiFetch(`claims`, {
    method: "post",
    body: claim.value
  }).then(() => {
    router.push('/claims/created');
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

  @media (min-width: 992px) {
    width: auto;
  }
}
</style>