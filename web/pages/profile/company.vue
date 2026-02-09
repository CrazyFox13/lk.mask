<template>
  <div>
    <NoConfirmation v-if="!user.email_verified_at"/>
    <CompanyForm
        v-else
        @inn-exists="companyExistsDialog=true"
    />
    <AlarmDialog v-if="onModerationDialog" @close="onModerationDialog=false">
      <template #header>
        <div class="dialog-head">
          <el-button class="close-btn text-black hidden-md-and-up" circle text @click="onModerationDialog=false">
            <SvgIcon name="close"/>
          </el-button>
          <el-button class="close-btn text-black hidden-sm-and-down" circle text @click="onModerationDialog=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center on-moderation">
          <div class="text-black modal-form-title m-0 text-center">Сообщение отправлено</div>
          <p>Мы получили ваше обращени, без внимания его не оставим. Свяжемся после проверки данных.</p>
          <el-button type="primary" @click="onModerationDialog=false">Спасибо</el-button>
        </div>
      </template>
    </AlarmDialog>

    <CompanyExistsDialog
        v-if="companyExistsDialog"
        @close="companyExistsDialog=false"
        @sent="companyExistsDialog=false;onModerationDialog=true"
    />
  </div>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import NoConfirmation from "~/components/Profile/Company/NoConfirmation.vue";
import CompanyForm from "~/components/Profile/Company/CompanyForm.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import CompanyExistsDialog from "~/components/Profile/Company/CompanyExistsDialog.vue";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";

const {user} = storeToRefs(useAuthStore());
const onModerationDialog = ref(false);
const companyExistsDialog = ref(false);


</script>

<style scoped lang="scss">
.on-moderation {
  .el-button {
    width: 100%;

    @media (min-width: 992px) {
      width: auto;
    }
  }
}
</style>