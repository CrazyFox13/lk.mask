<template>
  <el-card>
    <div class="flex justify-between mb-20">
      <div class="flex align-items-center">
        <el-avatar
            :alt="recommendation.company.title"
            :src="recommendation.author.avatar?recommendation.author.avatar:DefaultAvatar"
        />
        <div class="flex flex-column">
          <h4 class="text-h4 title-with-icon">
            <SvgIcon name="verification" v-if="recommendation.company.verified"/>
            {{ recommendation.company.title }}
          </h4>
          <p>{{ recommendation.author.name }} {{ recommendation.author.surname }}</p>
        </div>
      </div>
      <span class="is-new hidden-sm-and-down"
            v-if="isForMe &&  !recommendation.target_viewed_at"></span>
      <el-dropdown placement="top-start" class="hidden-md-and-up ml-auto" v-if="canDelete">
        <el-button link class="dropdown-button ">
          <SvgIcon name="more"/>
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item>
              <el-button size="small" link @click="confirmDeleteDialog=true">
                <SvgIcon width="19px" height="19px" name="trash" class="mr-2 text-gray"/>
                Удалить
              </el-button>
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
    <p v-html="short ? truncate(recommendation.text, maxShortLength) : recommendation.text"></p>
    <el-link type="primary" class="mt-10" v-if="isLong" @click="short=!short">
      {{ short ? 'Читать далее' : 'Свернуть' }}
    </el-link>
    <div class="flex align-items-center justify-between mt-20">
      <div class="hidden-sm-and-down">
        <el-button v-if="canDelete" link size="small" @click="confirmDeleteDialog=true">
          <SvgIcon name="trash" class="mr-2"/>
          Удалить
        </el-button>
      </div>
      <p class="text-subtitle text-gray ">{{ ruMoment(recommendation.created_at).fromNow() }}</p>
      <span class="is-new hidden-md-and-up" v-if="!recommendation.target_viewed_at"></span>
    </div>


    <AlarmDialog v-if="confirmDeleteDialog">
      <template #header>
        <div class="confirmation-header">
          <el-button class="text-black" circle text @click="confirmDeleteDialog=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <h4 class="text-black modal-form-title m-0 text-center">Вы уверены, что хотите удалить рекомендацию?</h4>
          <div class="confirmation-actions">
            <el-button type="primary" @click="destroy()">Удалить</el-button>
            <el-button type="primary" plain @click="confirmDeleteDialog=false">Отменить</el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>
  </el-card>
</template>

<script setup lang="ts">
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import {truncate, ruMoment, apiFetch, computed} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {useAuthStore} from "~/stores/user";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";

const props = defineProps(['recommendation']);
const emit = defineEmits(['removed']);
const maxShortLength = 300;
const isLong = props.recommendation.text.length > maxShortLength;
const short = ref(true);
const {user} = useAuthStore();
const confirmDeleteDialog = ref(false);

const canDelete = computed(() => {
  if (isForMe.value) return true;
  return props.recommendation.author_user_id === user!.id;
});

const isForMe = computed(() => {
  return props.recommendation.target_user_id === user!.id;
})

const destroy = () => {
  apiFetch(`recommendations/${props.recommendation.id}`, {
    method: "DELETE"
  }).then(() => {
    emit('removed')
  });
}
</script>

<style scoped lang="scss">
.el-avatar {
  width: 56px;
  height: 56px;
  margin-right: 10px;
  @media (min-width: 992px) {
    width: 64px;
    height: 64px;
    margin-right: 16px;
  }
}

.el-button--small {
  padding-left: 0 !important;
  padding-right: 0 !important;
}

h4, p {
  margin: 0;
}

.dropdown-button {
  border-radius: 6px;
  height: 24px;
  width: 24px;
  padding: 0 !important;
}

.is-new {
  display: block;
  width: 20px;
  height: 20px;
  background: #F92609;
  border-radius: 20px;
}
</style>