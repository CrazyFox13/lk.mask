<template>
  <div>
    <el-card>
      <div class="text-h4 mb-10">
        Ваше предложение:
      </div>
      <div v-if="isDeclined" class="declined-offer-info mb-10">
        <div class="text-h5 mb-5 text-error">Отклонено</div>
        <div v-if="declineReasonLabel" class="decline-reason mb-10">
          <p class="mb-0 text-gray">{{ declineReasonLabel }}</p>
        </div>
      </div>
      <OrderPrices
          class="mb-10"
          :amount_account_vat="offer.amount_account_vat"
          :amount_account="offer.amount_account"
          :amount_cash="offer.amount_cash"
          :amount_by_agreement="offer.amount_by_agreement"
      />
      <div v-if="offer.date_start" class="offer-date mb-10">
        <SvgIcon name="clock" class="mr-2"/>
        <div>Готов приступать с {{ moment(offer.date_start).format("DD.MM.YYYY") }}</div>
      </div>
      <div v-if="offer.comment" class="offer-comment mb-10">
        {{ offer.comment }}
      </div>
      <el-link role="button" class="mr-5" @click="onEdit()">
        <SvgIcon name="pencil" class="mr-2"/>
        Редактировать
      </el-link>
      <el-link role="button" @click="confirmDeleteDialog=true">
        <SvgIcon name="trash" class="mr-2"/>
        Удалить
      </el-link>
    </el-card>

    <AlarmDialog v-if="confirmDeleteDialog">
      <template #header>
        <div class="confirmation-header">
          <el-button style="display: inline-flex" class="text-black" circle text @click="confirmDeleteDialog=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <div class="text-black modal-form-title m-0 text-center">Вы уверены, что хотите удалить предложение?</div>
          <div class="confirmation-actions">
            <el-button type="primary" @click="onDelete()">Удалить</el-button>
            <el-button type="primary" plain @click="confirmDeleteDialog=false">Отменить</el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>
  </div>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";
import OrderPrices from "~/components/Orders/OrderPrices.vue";
import {apiFetch} from "~/composables/apiFetch";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";
import {computed} from "#imports";
import moment from "moment";

const props = defineProps(['offer']);
const emit = defineEmits(["deleted"]);
const confirmDeleteDialog = ref(false)

const declineReasonLabels: Record<string, string> = {
  'works_canceled': 'Отменились работы',
  'found_equipment': 'Нашли технику',
  'high_price': 'Высокая стоимость',
  'bad_terms': 'Не устроили сроки',
  'other': 'Другое'
};

const isDeclined = computed(() => {
  return props.offer?.status === 'declined';
});

const declineReasonLabel = computed(() => {
  if (!props.offer?.decline_reason) return null;
  return declineReasonLabels[props.offer.decline_reason] || props.offer.decline_reason;
});

const onEdit = () => {
  emitter.emit("offer-modal")
}

const onDelete = () => {
  apiFetch(`orders/${props.offer.order_id}/order-offers/${props.offer.id}`, {
    method: "delete"
  }).then(() => {
    emit("deleted");
  })
}

</script>

<style scoped lang="scss">
.text-h4 {
  text-decoration: none;
  color: inherit;
}

.avatar {
  width: 72px;
  height: 72px;
}

.company-container {
  flex: 1;
}

.w-100 {
  width: 100%;
}

.blur {
  filter: blur(3px);
}

.buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  column-gap: 12px;

  .el-button {
    width: 100%;
    padding: 8px 10px;
    margin: 0;
  }
}

.declined-offer-info {
  padding-bottom: 10px;
  border-bottom: 1px solid var(--el-border-color-light);
}

.text-error {
  color: var(--el-color-error);
  font-weight: 500;
}

.decline-reason {
  margin-top: 10px;
}

.text-subtitle {
  font-size: 14px;
  font-weight: 500;
  color: var(--el-text-color-primary);
}

.offer-date {
  display: flex;
  align-items: center;
  color: var(--el-text-color-regular);
}

.offer-comment {
  color: var(--el-text-color-regular);
  line-height: 1.5;
}
</style>