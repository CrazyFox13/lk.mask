<template>
  <el-card>
    <div class="flex justify-between mb-20">
      <div class="flex align-items-center" style="width:100%">
        <el-image :src="avatar" alt="avatar" class="mr-3"/>
        <div>
          <p class="text-sub-subtitle text-gray mb-0 mt-0">
            № {{ report.id }}
          </p>
          <div class="text-h4 title-with-icon mb-0 mt-0">
            <SvgIcon name="verification" v-if="company.verified"/>
            {{ company.title }}
          </div>
          <p class="mb-0 mt-0">{{ report.author.name }} {{ report.author.surname }}</p>
        </div>
        <el-dropdown placement="top-start" class="hidden-md-and-up ml-auto">
          <el-button link class="dropdown-button ">
            <SvgIcon name="more"/>
          </el-button>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item v-for="(action,k) in activeActions" :key="k">
                <el-button size="small" link @click="action.handler()">
                  <SvgIcon width="19px" height="19px" :name="action.icon" class="mr-2 text-gray"/>
                  {{ action.label }}
                </el-button>
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
      <TabBadge class="hidden-sm-and-down" :value="report.new_messages_count"/>
    </div>
    <div class="type-sum-info">
      <div class="text-h5 mr-30 flex align-items-center">
        <SvgIcon name="report-type" class="mr-2"/>
        Тема претензии: {{ report.type.title }}
      </div>
      <div class="text-h5">
        <SvgIcon name="payment" class="mr-2"/>
        Сумма претензии: {{ formatPrice(report.amount) }}
      </div>
    </div>
    <div class="mb-20" v-html="short ? truncate(report.text, maxShortLength) : report.text"></div>
    <div class="flex mt-20 referee" v-if="report.referee_conclusion">
      <div class="referee-avatar">
        <el-avatar alt="referee" :src="RefereeAvatar"/>
        <p class="text-hint">Арбитр</p>
      </div>
      <div style="display: inline-block;">
        <SvgIcon name="referee" class="mr-2 text-orange"/>
        <span v-html="short ? truncate(report.referee_conclusion, maxShortLength) : report.referee_conclusion"></span>
      </div>
    </div>
    <el-link type="primary" class="mt-10" v-if="isLong" @click="short=!short">
      {{ short ? 'Читать далее' : 'Свернуть' }}
    </el-link>
    <div class="flex align-items-center mt-20 justify-content-between" style="width: 100%;">
      <div class="flex align-items-center hidden-sm-and-down">
        <el-button size="small" link v-for="(action,k) in activeActions" :key="action.icon" @click="action.handler()">
          <SvgIcon width="19px" height="19px" :name="action.icon" class="mr-2 text-gray"/>
          {{ action.label }}
        </el-button>
      </div>
      <div class="flex align-items-center ml-md-auto">
        <p class="text-gray mr-10">{{ ruMoment(report.created_at).fromNow() }}</p>
        <p v-if="status" class="flex align-items-center">
          <SvgIcon
              v-if="status.icon" class="mr-1"
              :name="status.icon"
              :key="status.icon"
              v-bind:class="{'text-orange':value.status==='referee'}"
          />
          {{ status.label }}
        </p>
      </div>
      <TabBadge class="hidden-md-and-up ml-auto" :value="report.new_messages_count"/>
    </div>

    <EditReportDialog
        v-if="editDialog"
        v-model="value"
        @close="editDialog=false"
    />

    <AlarmDialog v-if="confirmCancel" @close="confirmCancel=false">
      <template #header>
        <div class="confirmation-header">
          <el-button class="text-black" circle text @click="confirmCancel=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <div class="text-black modal-form-title m-0 text-center">Вы уверены, что хотите отозвать претензию?</div>
          <div class="confirmation-actions">
            <el-button type="primary" @click="cancel()">Отозвать</el-button>
            <el-button type="primary" plain @click="confirmCancel=false">Отменить</el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>
  </el-card>
</template>

<script setup lang="ts">
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import RefereeAvatar from "~/assets/images/referee_avatar.png";
import SvgIcon from "~/components/SvgIcon.vue";
import {formatPrice, truncate, ruMoment, computed, apiFetch, emitter} from "#imports";
import {useReportStore} from "~/stores/report";
import {useAuthStore} from "~/stores/user";
import EditReportDialog from "~/components/Profile/Reports/EditReportDialog.vue";
import TabBadge from "~/components/Common/TabBadge.vue";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";

const props = defineProps(['report']);
const value = ref(props.report);
const emit = defineEmits(['removed']);

const maxShortLength = 150;
const isLong = value.value.text.length > maxShortLength;
const short = ref(true);
const editDialog = ref(false);
const confirmCancel = ref(false);

const company = value.value.company;
const avatar = value.value.author?.avatar ? value.value.author.avatar : DefaultAvatar;
const {getStatusByKey} = useReportStore();
const status = computed(() => {
  return getStatusByKey(value.value.status);
});
const {user} = useAuthStore();


const showRefereeButton = computed(() => {
  return value.value.status === 'active';
});

const showEditButton = computed(() => {
  return value.value.status !== 'confirmed' &&
      value.value.status !== 'canceled' &&
      value.value.status !== "rejected" &&
      value.value.status !== "resolved" &&
      value.value.author_user_id === user!.id;
});

const showCancelButton = computed(() => {
  return value.value.status !== 'confirmed' &&
      value.value.status !== 'canceled' &&
      value.value.status !== "rejected" &&
      value.value.status !== "resolved" &&
      value.value.author_user_id === user!.id;
});

const showMessageButton = computed(() => {
  return value.value.status !== "rejected" && value.value.status !== "resolved" && value.value.chat;
});

const showResolveButton = computed(() => {
  return value.value.status === '' // todo:
})

const showDeleleButton = computed(() => {
  return value.value.status === 'canceled';
});

const actions = computed(() => {
  return [
    {
      icon: 'referee', label: 'Пригласить арбитра', handler: () => {
        apiFetch(`reports/${value.value.id}/referee`, {
          method: "POST"
        }).then(() => {
          value.value.status = 'referee';
        });
      }, visible: showRefereeButton.value
    },
    {
      icon: 'comment', label: 'Сообщение', handler: () => {
        emitter.emit('chat', value.value.chat);
      }, visible: showMessageButton.value
    },
    {
      icon: 'pencil', label: 'Редактировать', handler: () => {
        editDialog.value = true;
      }, visible: showEditButton.value
    },
    {
      icon: 'redo', label: 'Отозвать', handler: () => {
        confirmCancel.value = true;
      }, visible: showCancelButton.value
    },
    {
      icon: 'resolve', label: 'Урегулировать', handler: () => {
        apiFetch(`reports/${value.value.id}/resolve`, {
          method: "POST"
        }).then(() => {
          value.value.status = 'resolved';
          emit('removed');
        });
      }, visible: showResolveButton.value
    },
    {
      icon: 'trash', label: 'Удалить', handler: () => {
        apiFetch(`reports/${value.value.id}`, {
          method: "DELETE"
        }).then(() => {
          emit('removed');
        });
      }, visible: showDeleleButton.value
    },
  ];
});

const activeActions = computed(() => {
  return actions.value.filter(i => i.visible);
});

const cancel = () => {
  apiFetch(`reports/${value.value.id}/cancel`, {
    method: "POST"
  }).then(() => {
    value.value.status = 'canceled';
    confirmCancel.value = false;
    emit('removed');
  });
}

</script>

<style scoped lang="scss">
.el-image {
  width: 56px;
  height: 56px;
  @media (min-width: 992px) {
    width: 64px;
    height: 64px;
  }
}

.el-button--small {
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.dropdown-button {
  border-radius: 6px;
  padding: 7px 7px !important;
}

//flex flex-column flex-md-row align-items-center mb-20

.type-sum-info {
  display: flex;
  flex-flow: column;
  margin-bottom: 20px;
  row-gap: 13px;
  @media (min-width: 992px) {
    flex-flow: row;
    align-items: center;
  }
}


.referee {
  background: #E8EBF1;
  border-radius: 6px;
  padding: 16px 20px;

  .referee-avatar {
    margin-right: 25px;
    text-align: center;
    justify-content: center;
    align-items: center;

    .el-avatar {
      margin: 0;
      width: 35px;
      height: 35px;
    }
  }
}
</style>