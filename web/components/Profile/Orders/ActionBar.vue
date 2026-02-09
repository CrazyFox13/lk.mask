<template>
  <div class="flex align-items-center justify-between">
    <nuxt-link v-if="inProfile" to="/profile/orders" class="el-button el-button--info el-button--small">
      <SvgIcon name="back"/>
    </nuxt-link>

    <OfferLink :order="order" class="hidden-md-and-up"/>

    <div class="hidden-sm-and-down" v-bind:class="{'ml-auto':!inProfile}">
      <OfferLink :order="order"/>


      <!-- Для поставщиков (владельцев или исполнителей) - выпадающий список статусов -->
      <el-dropdown v-if="canChangeStatus" trigger="click" @command="handleStatusChange" class="mr-2">
        <span class="el-button el-button--info el-button--small status-pill status-dropdown">
          <SvgIcon v-if="statusIcon" :name="statusIcon" class="mr-2"/>
          {{ statusLabel }}
          <SvgIcon name="chevron-down" class="ml-2"/>
        </span>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item 
              v-for="status in availableStatuses" 
              :key="status.value" 
              :command="status.value"
              :disabled="order.moderation_status === status.value"
            >
              <SvgIcon v-if="status.icon" :name="status.icon" class="mr-2"/>
              {{ status.label }}
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
      <!-- Для заказчиков - просто отображение статуса -->
      <span v-else class="el-button el-button--info el-button--small mr-2 status-pill">
        <SvgIcon v-if="statusIcon" :name="statusIcon" class="mr-2"/>
        {{ statusLabel }}
      </span>

      <el-button
          type="info"
          class="mr-2 el-button--small"
          v-if="canCancel"
          @click="showCancelDialog = true">
        Отменить заявку
      </el-button>
      <el-button
          type="info"
          class="mr-2 el-button--small"
          v-else-if="!isCustomerCompany && (order.moderation_status==='removed' || order.moderation_status==='completed')"
          @click="moderate()">
        Опубликовать
      </el-button>

      <nuxt-link
          :to="`/orders/${order.id}/edit`"
          class="el-button el-button--info el-button--small"
          v-if="canEdit"
      >
        <SvgIcon name="pencil"/>
      </nuxt-link>
      <el-button
          type="info"
          class="mr-2 el-button--small"
          @click="configDeleteDialog=true"
          v-if="canDelete"
      >
        <SvgIcon name="trash"/>
      </el-button>
    </div>
    <el-dropdown placement="top-start" class="hidden-md-and-up" v-bind:class="{'ml-auto':!inProfile}">
      <el-button class="tab-link">
        <SvgIcon name="more"/>
      </el-button>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item v-if="!canChangeStatus">
            <span class="el-button is-link el-button--small status-pill">
              <SvgIcon v-if="statusIcon" :name="statusIcon" class="mr-2"/>
              {{ statusLabel }}
            </span>
          </el-dropdown-item>
          <!-- Для поставщиков (владельцев или исполнителей) - список статусов -->
          <template v-if="canChangeStatus">
            <el-dropdown-item 
              v-for="status in availableStatuses" 
              :key="status.value"
              @click="handleStatusChange(status.value)"
              :disabled="order.moderation_status === status.value"
            >
              <span class="el-button is-link el-button--small" :class="{'text-primary': order.moderation_status === status.value}">
                <SvgIcon v-if="status.icon" :name="status.icon" class="mr-2"/>
                {{ status.label }}
              </span>
            </el-dropdown-item>
          </template>
          <el-dropdown-item v-if="canCancel">
            <el-button link @click="showCancelDialog = true" class="el-button--small">
              <SvgIcon name="remove-list" class="mr-2"/>
              Отменить заявку
            </el-button>
          </el-dropdown-item>
          <el-dropdown-item v-if="!isCustomerCompany && (order.moderation_status==='removed' || order.moderation_status==='completed')">
            <el-button link @click="moderate()" class="el-button--small">
              <SvgIcon name="redo" class="mr-2"/>
              Опубликовать
            </el-button>
          </el-dropdown-item>
          <el-dropdown-item v-if="canEdit">
            <nuxt-link
                :to="`/orders/${order.id}/edit`"
                class="el-button is-link el-button--small">
              <SvgIcon name="pencil" class="mr-2"/>
              Редактировать
            </nuxt-link>
          </el-dropdown-item>
          <el-dropdown-item v-if="canDelete">
            <el-button
                link
                @click="configDeleteDialog=true"
                class="el-button--small">
              <SvgIcon name="trash" class="mr-2"/>
              Удалить
            </el-button>
          </el-dropdown-item>

        </el-dropdown-menu>
      </template>
    </el-dropdown>

    <AlarmDialog v-if="configDeleteDialog">
      <template #header>
        <div class="confirmation-header">
          <el-button class="text-black" circle text @click="configDeleteDialog=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <div class="text-black modal-form-title m-0 text-center">Вы уверены, что хотите удалить заявку?</div>
          <div class="confirmation-actions">
            <el-button type="primary" @click="destroy()">Удалить</el-button>
            <el-button type="primary" plain @click="configDeleteDialog=false">Отменить</el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>

    <!-- Модальное окно отмены заявки для заказчиков -->
    <AlarmDialog v-if="showCancelDialog && isCustomerCompany">
      <template #header>
        <div class="confirmation-header">
          <el-button class="text-black" circle text @click="closeCancelDialog()">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <div class="text-black modal-form-title m-0 text-center mb-20">Точно отменить Заявку?</div>
          
          <div class="cancel-reason-select mb-20">
            <el-select 
              v-model="cancelReason" 
              placeholder="Выберите причину отмены"
              style="width: 100%"
              size="large">
              <el-option 
                label="Отменились работы" 
                value="works_canceled" />
              <el-option 
                label="Нашли технику" 
                value="found_equipment" />
              <el-option 
                label="Другое" 
                value="other" />
            </el-select>
          </div>

          <div class="confirmation-actions">
            <el-button 
              type="primary" 
              @click.prevent="confirmCancel()"
              :disabled="!cancelReason">
              Отменить
            </el-button>
            <el-button 
              type="primary" 
              plain 
              @click.prevent="closeCancelDialog()">
              Оставить
            </el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, computed, useRoute, useRouter, ElMessage, watch} from "#imports";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";
import BadgeLabel from "~/components/Common/BadgeLabel.vue";
import OfferLink from "~/components/Profile/Orders/OfferLink.vue";
import {useAuthStore} from "~/stores/user";

const props = defineProps(['order']);
const emit = defineEmits(['removed', 'moderate', 'complete', 'statusChanged']);
const configDeleteDialog = ref(false);
const showCancelDialog = ref(false);
const cancelReason = ref<string | null>(null);
const router = useRouter();
const {user} = useAuthStore();

const route = useRoute()
const inProfile = computed(() => {
  return route.path.includes("profile");
})

const isCustomerCompany = computed(() => {
  return user?.company?.company_type_id === 3;
});

// Является ли текущая компания исполнителем (имеет принятое предложение)
const isContractor = computed(() => {
  return props.order.is_contractor === true;
});

const canEdit = computed(() => {
  if (!isCustomerCompany.value) return true;
  return ['draft', 'moderation', 'approved', 'on_approval'].includes(props.order.moderation_status);
});

const canDelete = computed(() => {
  if (!isCustomerCompany.value) return props.order.moderation_status !== 'approved';
  return props.order.moderation_status === 'draft';
});

const canCancel = computed(() => {
  if (!isCustomerCompany.value) return true;
  return ['moderation', 'approved', 'on_approval'].includes(props.order.moderation_status);
});

const statusMap: Record<string, {label: string; icon?: string}> = {
  draft: {label: 'Черновик'},
  moderation: {label: 'На модерации', icon: 'clock'},
  on_approval: {label: 'На согласовании', icon: 'clock'},
  approved: {label: 'Новая заявка', icon: 'report-confirmed'},
  in_progress: {label: 'В работе', icon: 'in-progress'},
  canceled: {label: 'Отменённая', icon: 'form-uncheck'},
  removed: {label: 'Снято с публикации', icon: 'remove-list'},
  completed: {label: 'Завершённая', icon: 'complete'},
};

const statusMeta = computed(() => {
  return statusMap[props.order.moderation_status] || {label: 'Статус'};
});

const statusLabel = computed(() => statusMeta.value.label);
const statusIcon = computed(() => statusMeta.value.icon || '');

// Статусы доступные для выбора поставщиком
const availableStatuses = computed(() => {
  const statuses = [
    { value: 'approved', label: 'Новая заявка', icon: 'report-confirmed' },
    { value: 'on_approval', label: 'На согласовании', icon: 'clock' },
    { value: 'in_progress', label: 'В работе', icon: 'in-progress' },
    { value: 'canceled', label: 'Отменённая', icon: 'form-uncheck' },
    { value: 'completed', label: 'Завершённая', icon: 'complete' },
  ];
  return statuses;
});

// Можно ли менять статус (для поставщиков - владельцев или исполнителей)
const canChangeStatus = computed(() => {
  const validStatus = ['approved', 'on_approval', 'in_progress', 'canceled', 'completed'].includes(props.order.moderation_status);
  const isOwnerSupplier = !isCustomerCompany.value; // владелец-поставщик
  return validStatus && (isOwnerSupplier || isContractor.value);
});

const destroy = () => {
  apiFetch(`orders/${props.order.id}`, {
    method: "DELETE"
  }).then(() => {
    router.push('/profile/orders');
  })
}

const cancel = () => {
  // Для заказчиков показываем модальное окно с выбором причины
  if (isCustomerCompany.value) {
    showCancelDialog.value = true;
    return;
  }
  
  // Для поставщиков отменяем сразу без причины
  apiFetch(`orders/${props.order.id}/cancel`, {
    method: "POST"
  }).then(() => {
    emit('removed');
  })
}

const closeCancelDialog = () => {
  showCancelDialog.value = false;
  cancelReason.value = null;
}

// Отслеживаем изменения cancelReason для отладки
watch(cancelReason, (newValue) => {
  console.log('cancelReason изменен:', newValue);
});

const confirmCancel = async () => {
  console.log('confirmCancel вызван, cancelReason:', cancelReason.value);
  
  if (!cancelReason.value) {
    console.error('Причина отмены не выбрана');
    ElMessage.warning('Пожалуйста, выберите причину отмены');
    return;
  }
  
  console.log('Отмена заявки:', {
    orderId: props.order.id,
    cancelReason: cancelReason.value
  });
  
  try {
    await apiFetch(`orders/${props.order.id}/cancel`, {
      method: "POST",
      body: {
        cancel_reason: cancelReason.value
      }
    });
    
    console.log('Заявка успешно отменена');
    ElMessage.success('Заявка отменена');
    closeCancelDialog();
    emit('removed');
  } catch (error: any) {
    console.error('Ошибка при отмене заявки:', error);
    
    // Обработка ошибок валидации
    if (error?.status === 422) {
      const validationErrors = error?.body?.errors;
      if (validationErrors) {
        const firstError = Object.values(validationErrors)[0];
        ElMessage.error(Array.isArray(firstError) ? firstError[0] : firstError);
      } else {
        ElMessage.error('Ошибка валидации при отмене заявки');
      }
    } else if (error?.status === 403) {
      ElMessage.error('У вас нет прав для отмены этой заявки');
    } else {
      ElMessage.error(error?.body?.message || 'Ошибка при отмене заявки');
    }
  }
}

const moderate = () => {
  apiFetch(`orders/${props.order.id}/moderate`, {
    method: "POST"
  }).then(({status}) => {
    emit('moderate', status);
  })
}

const handleStatusChange = (status: string) => {
  if (status === props.order.moderation_status) return;
  
  apiFetch(`orders/${props.order.id}/set-status`, {
    method: "POST",
    body: { status }
  }).then(() => {
    emit('statusChanged', status);
  })
}

</script>

<style scoped lang="scss">
.el-button {
  border-radius: 6px;
  padding: 7px 7px !important;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: default;
  border: none;
  background: transparent;
  color: #222222;
  height: 32px;
  line-height: 1;
  box-sizing: border-box;
  vertical-align: middle;
}

.status-dropdown {
  cursor: pointer;
  
  &:hover {
    background: #f5f5f5;
  }
}

.text-primary {
  color: var(--el-color-primary);
  font-weight: 600;
}

.cancel-reason-select {
  max-width: 400px;
  margin: 0 auto;
}

.confirmation-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}
</style>