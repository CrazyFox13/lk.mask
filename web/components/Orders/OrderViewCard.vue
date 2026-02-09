<template>
  <el-card
      class="order-card"
      v-bind:class="{'viewed':order.is_viewed}"
  >
    <!-- Кнопка назад всегда отображается в профиле -->
    <div v-if="inProfile && !canEdit" class="action-bar">
      <nuxt-link to="/profile/orders" class="el-button el-button--info el-button--small back-button">
        <SvgIcon name="back"/>
      </nuxt-link>
    </div>
    <ActionBar
        v-if="canEdit"
        class="action-bar"
        :order="order"
        @moderate="status=>emit('moderate',status)"
        @removed="emit('removed')"
        @complete="emit('complete')"
        @statusChanged="status=>emit('statusChanged',status)"
    />

    <div class="flex">
      <div class="flex-1">
        <div class="text-sub-subtitle text-gray flex align-center mb-6">
          <p class="text-orange mb-0 mt-0" v-if="isRemoved">Снято с публикации</p>
          <p v-else class="mb-0 mt-0" v-bind:class="{'hide-xs':isCompleted || isOnApproval || isInProgress}">
            Опубликовано {{ ruMoment(order.publish_date || order.created_at).format("DD MMMM YYYY HH:mm") }}
          </p>
          <div class="ml-3">
            <SvgIcon name="eye"/>
            {{ order.views_count }}
          </div>
          <div class="on-approval" v-if="isOnApproval">На согласовании</div>
          <div class="in-progress" v-if="isInProgress">В работе</div>
          <div class="completed" v-if="isCompleted">Завершённая</div>
        </div>
        <h1 v-bind:class="{'closed':isClosed}" class="request-title mt-0 mb-10">{{ order.title }}</h1>
        <OrderDuration class="text-gray" :order="order"/>
        <div v-if="showCancelReason" class="cancel-reason-info mt-10">
          <div class="text-h5 mb-5">Причина отмены:</div>
          <p class="mb-0 text-gray">{{ cancelReasonLabel }}</p>
        </div>
      </div>
      <MiniButtonsBar
          v-if="!inProfile"
          :order="order"
          @report="emit('report')"
      />
    </div>
    <div class="flex order-section" v-bind:class="{'closed':isClosed}">
      <div class="text-h5">Стоимость: &nbsp;</div>
      <p class="mb-0 mt-0">{{ order.payment_unit ? order.payment_unit.name : '' }}</p>
    </div>
    <OrderPrices
        class="mb-2"
        :amount_account_vat="order.amount_account_vat"
        :amount_account="order.amount_account"
        :amount_cash="order.amount_cash"
        :amount_by_agreement="order.amount_by_agreement"
        v-bind:class="{'closed':isClosed}"
    />
    <div class="flex flex-wrap order-section" v-bind:class="{'closed':isClosed}">
      <div class="width-50 w-md-25">
        <div class="text-h5">Начало работ: </div>
        <p class="mb-0 mt-0">{{ moment(order.start_date).format("DD.MM.YYYY HH:mm") }}</p>
      </div>
      <div class="width-50 w-md-25" v-if="order.finish_date">
        <div class="text-h5">Завершение работ</div>
        <p class="mb-0 mt-0">{{ moment(order.finish_date).format("DD.MM.YYYY HH:mm") }}</p>
      </div>
      <div v-if="livingAnswer" class="flex align-items-center width-50 w-md-25 flex-md-col mt-xs-10">
        <div class="text-h5 mb-0">Проживание: &nbsp;</div>
        <SvgIcon name="form-check" v-if="livingAnswer==='true'"/>
        <SvgIcon name="form-uncheck" v-else/>
      </div>
      <div v-if="securityAnswer" class="flex align-items-center width-50 w-md-25 flex-md-col mt-xs-10">
        <div class="text-h5 mb-0">Охрана: &nbsp;</div>
        <SvgIcon name="form-check" v-if="securityAnswer==='true'"/>
        <SvgIcon name="form-uncheck" v-else/>
      </div>
    </div>

    <div class="text-h5 order-section mb-0" v-bind:class="{'closed':isClosed}">Описание:</div>
    <p class="mb-0 mt-0" v-bind:class="{'closed':isClosed}">{{ order.description }}</p>
    <div class="text-h5 order-section mb-10 mt-0" v-bind:class="{'closed':isClosed}"
        v-if="order.addresses && order.addresses.length">
      Адрес:
    </div>
    <div v-for="(address,i) in order.addresses" class="flex mr-2" :key="address.id" v-bind:class="{'closed':isClosed}">
      <SvgIcon name="location" v-if="order.addresses.length===1"/>
      <SvgIcon name="empty-marker" class="text-gray text-hint" v-else margin="-3px 0 0 0">
        <template #content>
          {{ markerIndexes[i] }}
        </template>
      </SvgIcon>
      <p class="ml-2 mt-0">{{ address.address }}</p>
    </div>

    <client-only>
      <OrderMap
          class="order-section"
          v-bind:class="{'closed':isClosed}"
          :addresses="order.addresses"
          :vehicleGroupId="order.vehicle_type.vehicle_group_id"
      />
    </client-only>
    <div class="text-h5 order-section mb-10" v-if="order.documents && order.documents.length">
      Материалы:
    </div>
    <div class="flex flex-column flex-wrap flex-md-row" style="flex-wrap: wrap">
      <DocumentItem
          :document="document"
          v-for="document in order.documents"
          :key="document.id"
      />
    </div>
  </el-card>
</template>

<script setup lang="ts">
import moment from "moment";
import SvgIcon from "~/components/SvgIcon.vue";
import OrderPrices from "~/components/Orders/OrderPrices.vue";
import OrderDuration from "~/components/Orders/OrderDuration.vue";
import MiniButtonsBar from "~/components/Orders/MiniButtonsBar.vue";
import {computed, ruMoment, useRoute} from "#imports";
import {storeToRefs} from "pinia";
import {useOrderStore} from "~/stores/order";
import ActionBar from "~/components/Profile/Orders/ActionBar.vue";
import DocumentItem from "~/components/Common/DocumentItem.vue";
import {useAuthStore} from "~/stores/user";
import type {IAddress} from "~/types/address";
import OrderMap from "~/components/Common/OrderMap.vue";

const props = defineProps(['order']);
const emit = defineEmits(['report', 'removed', 'moderate', 'complete', 'statusChanged']);
const {markerIndexes} = storeToRefs(useOrderStore());

const livingAnswer = computed(() => {
  return props.order.form_answers.find((a: any) => a.question.type === 'living');
});
const securityAnswer = computed(() => {
  return props.order.form_answers.find((a: any) => a.question.type === 'security');
});
const route = useRoute();
const inProfile = computed(() => {
  return route.path.includes("profile");
})
const {user} = useAuthStore();
const isCustomerCompany = computed(() => {
  return user?.company?.company_type_id === 3;
});

// Является ли текущая компания исполнителем (имеет принятое предложение)
const isContractor = computed(() => {
  return props.order.is_contractor === true;
});

const canEdit = computed(() => {
  if (!user) return false;
  
  // Исполнитель может менять статус на любой странице (публичной или в профиле)
  if (isContractor.value) return true;
  
  // Остальные могут редактировать только в профиле
  if (!inProfile.value) return false;
  
  const isOwner = props.order.user_id === user.id;
  const isBoss = props.order.company_id === user.company_id && user.company_role === 'boss';
  if (!isOwner && !isBoss) return false;
  if (!isCustomerCompany.value) return true;
  return ['draft', 'moderation', 'approved', 'on_approval'].includes(props.order.moderation_status);
});


const isCompleted = computed(() => {
  return props.order?.moderation_status === 'completed'
});
const isOnApproval = computed(() => {
  return props.order?.moderation_status === 'on_approval'
});
const isInProgress = computed(() => {
  return props.order?.moderation_status === 'in_progress'
});
const isRemoved = computed(() => {
  return props.order?.moderation_status === 'removed'
});

const isClosed = computed(() => {
  return isRemoved.value || isCompleted.value
})

const isCanceled = computed(() => {
  return props.order?.moderation_status === 'canceled'
})

const cancelReasonLabels: Record<string, string> = {
  'works_canceled': 'Отменились работы',
  'found_equipment': 'Нашли технику',
  'other': 'Другое'
}

const cancelReasonLabel = computed(() => {
  if (!props.order?.cancel_reason) return null;
  return cancelReasonLabels[props.order.cancel_reason] || props.order.cancel_reason;
})

// Показывать причину отмены для поставщиков (не заказчиков)
const showCancelReason = computed(() => {
  return isCanceled.value && !isCustomerCompany.value && cancelReasonLabel.value;
})

</script>

<style lang="scss">
.order-card {
  .back-button {
    border-radius: 6px;
    padding: 7px 7px !important;
  }

  .closed {
    opacity: 0.6;
  }

  .request-title.closed {
    text-decoration: line-through;
  }

  p, .request-title, .text-h5 {
    margin-top: 0;
  }

  p, .text-h5 {
    margin-bottom: 6px;
  }

  .request-title {
    margin-bottom: 9px;
  }

  .mt-xs-10 {
    @media (max-width: 991px) {
      margin-top: 10px;
    }
  }

  .width-50 {
    width: 50%;
  }

  .w-md-25 {
    @media (min-width: 992px) {
      width: 25%;
    }
  }

  .flex-md-col {
    @media (min-width: 992px) {
      flex-flow: column;
      align-items: flex-start;
      justify-content: space-between;
    }
  }

  .yandex-container {
    height: 185px;
    @media (min-width: 992px) {
      height: 242px;
    }
  }

  .flex-1 {
    flex: 1;
  }

  .order-section {
    margin-top: 20px;
    @media (min-width: 992px) {
      margin-top: 30px;
    }
  }

  .action-bar {
    margin-bottom: 20px;
    @media (min-width: 992px) {
      margin-bottom: 30px;
    }
  }
}

.hide-xs {
  display: none;
  @media (min-width: 992px) {
    display: block;
  }
}

.completed {
  margin-left: auto;
  margin-right: 5px;
  padding: 6px 10px;
  border-radius: 6px;
  background: var(--info-red-error, #F92609);
  font-size: 13px;
  font-weight: 500;
  line-height: 14px;
  color: white;
}

.on-approval {
  margin-left: auto;
  margin-right: 5px;
  padding: 6px 10px;
  border-radius: 6px;
  background: #FAB80F;
  font-size: 13px;
  font-weight: 500;
  line-height: 14px;
  color: white;
}

.in-progress {
  margin-left: auto;
  margin-right: 5px;
  padding: 6px 10px;
  border-radius: 6px;
  background: #67C23A;
  font-size: 13px;
  font-weight: 500;
  line-height: 14px;
  color: white;
}
</style>