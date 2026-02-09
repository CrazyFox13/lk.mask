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
          <div class="text-black modal-window-title text-center mb-10">Претензия на исполнителя</div>
        </div>
      </template>

      <div class="mb-10" v-bind:class="{'input-error':!!errors.report_type_id}">
        <div class="text-h6 mb-10">Тема претензии*</div>
        <ReportTypePicker v-model="report.report_type_id"/>
        <p v-if="!!errors.report_type_id">{{ errors.report_type_id }}</p>
      </div>

      <div class="mb-10" v-bind:class="{'input-error':!!errors.amount}">
        <div class="text-h6 mb-10">Сумма требований</div>
        <el-input
            autosize
            placeholder="Введите текст"
            v-model="report.amount"
        />
        <p v-if="!!errors.amount">{{ errors.amount }}</p>
      </div>

      <div class="mb-10" v-bind:class="{'input-error':!!errors.text}">
        <div class="text-h6 mb-10">Опишите причину претензии (минимум 15 символов)*</div>
        <el-input
            autosize
            type="textarea"
            placeholder="Введите текст"
            v-model="report.text"
            min="15"
        />
        <p v-if="!!errors.text">{{ errors.text }}</p>
      </div>

      <div>
        <div class="text-h6">Файлы к претензии:</div>
        <p class="mb-10 mt-2">
          Заявка, УПД, документы, подтверждающие передачу документов
        </p>
        <p class="text-gray text-subtitle mb-20 mt-0">
          Без подтверждающих документов решение через арбитра невозможно. <br/> Загрузите документы, подтверждающие
          нарушения
          условий, причиненный ущерб и т.п.
        </p>
        <div class="flex flex-wrap">
          <DocumentItem
              v-for="(document,i) in report.documents"
              :key="i"
              :document="document"
              :edit="true"
              @deleted="report.documents.splice(i,1)"
          />
        </div>
        <FileUploader
            @file_data="fileUploaded"
            :multiple="true"
        />
      </div>
      <div class="text-center">
        <el-button
            :disabled="!isFormReady"
            class="btn-submit mt-20"
            :type="isFormReady?'primary':'info'"
            @click="submit()"
        >
          Отправить жалобу
        </el-button>
      </div>
    </el-dialog>
  </client-only>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import FileUploader from "~/components/Common/Forms/FileUploader.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, computed, useRouter} from "#imports";
import DocumentItem from "~/components/Common/DocumentItem.vue";
import ReportTypePicker from "~/components/Common/Forms/ReportTypePicker.vue";

interface IReport {
  text: string
  documents: any[]
  report_type_id: number | undefined
  company_id: number
  target_user_id: number
}

const props = defineProps(['company']);
const emit = defineEmits(['close']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);
const router = useRouter();

const report = ref<IReport>({
  text: "",
  documents: [],
  report_type_id: undefined,
  company_id: props.company.id,
  target_user_id: props.company.boss.id,
});
const errors = ref<any>({})

const fileUploaded = (file: any) => {
  report.value.documents.push(file);
}

const isFormReady = computed(() => {
  return report.value.text.length > 15;
})

const submit = () => {
  errors.value = {};
  apiFetch(`reports`, {
    method: "post",
    body: report.value
  }).then(() => {
    router.push('/reports/created');
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
p {
  word-break: normal;
}

.el-select {
  width: 100%;
}
</style>