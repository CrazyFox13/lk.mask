<template>
  <client-only>
    <el-dialog
        v-if="order"
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
          <div class="text-black modal-window-title text-center mb-10">Предложение</div>
        </div>
      </template>

      <div v-if="offer && order && offer.order_id">
        <div>
          <label class="input-title mb-10 block">Безналичный расчет с НДС</label>
          <TextInput
              v-model="offer.amount_account_vat"
              placeholder="Введите сумму"
              type="number"
              :error="errors.amount_account_vat"
              :disabled="order?.no_haggling"
          >
            <template #suffix v-if="order?.no_haggling">
              Заявка без торга
            </template>
          </TextInput>
        </div>

        <div>
          <label class="input-title mb-10 block">Безналичный расчет без НДС</label>
          <TextInput
              v-model="offer.amount_account"
              placeholder="Введите сумму"
              type="number"
              :error="errors.amount_account"
              :disabled="order?.no_haggling"
          >
            <template #suffix v-if="order?.no_haggling">
              Заявка без торга
            </template>
          </TextInput>
        </div>

        <div>
          <label class="input-title mb-10 block">Наличный расчет</label>
          <TextInput
              v-model="offer.amount_cash"
              placeholder="Введите сумму"
              type="number"
              :error="errors.amount_cash"
              :disabled="order?.no_haggling"
          >
            <template #suffix v-if="order?.no_haggling">
              Заявка без торга
            </template>
          </TextInput>
        </div>

        <div>
          <label class="input-title mb-10 block">Готов приступить к работе</label>
          <DatePicker
              placeholder="Выбрать дату"
              v-model="offer.date_start"
              format="DD.MM.YYYY"
              :error="errors.date_start"
          />
        </div>

        <div class="mb-20" v-bind:class="{'input-error':!!errors.comment}">
          <label class="input-title mb-10 block">Комментарий</label>
          <el-input
              autosize
              type="textarea"
              placeholder="Введите текст"
              v-model="offer.comment"
              min="15"
          />
          <p v-if="!!errors.comment">{{ errors.comment }}</p>
        </div>

        <div class="text-center">
          <el-button
              class="mt-20 w-100"
              :type="'primary'"
              @click="submit()"
          >
            Отправить
          </el-button>
        </div>
      </div>
    </el-dialog>
  </client-only>
</template>

<script lang="ts" setup>
import SvgIcon from "../SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, ref, watchEffect, watch, computed, onMounted} from "#imports";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import DatePicker from "~/components/Common/Forms/DatePicker.vue";
import {type IOrderOffer} from "~/stores/order";

const props = defineProps(['order']);
const emit = defineEmits(['close']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);

const errors = ref<any>({})

const offer = ref<IOrderOffer>({
  id: undefined,
  comment: '',
  amount_account: 0,
  amount_account_vat: 0,
  amount_cash: 0,
  date_start: undefined,
  order_id: undefined,
  status: 'waiting',
  created_at: '',
});

watchEffect(() => {
  if (!offer.value) {
    offer.value = {
      id: undefined,
      comment: '',
      amount_account: 0,
      amount_account_vat: 0,
      amount_cash: 0,
      date_start: undefined,
      order_id: undefined,
      status: 'waiting',
      created_at: '',
    };
  }
  if (props.order && offer.value) {
    if (props.order.company_offer) {
      offer.value = {
        ...props.order.company_offer,
        amount_account: props.order.company_offer.amount_account ?? 0,
        amount_account_vat: props.order.company_offer.amount_account_vat ?? 0,
        amount_cash: props.order.company_offer.amount_cash ?? 0,
        comment: props.order.company_offer.comment || '',
        date_start: props.order.company_offer.date_start || props.order.start_date || undefined,
        order_id: props.order.company_offer.order_id || props.order.id,
        status: props.order.company_offer.status || 'waiting',
        created_at: props.order.company_offer.created_at || '',
      };
    } else {
      if (!offer.value.order_id && props.order.id) {
        offer.value.order_id = props.order.id;
      }
      offer.value.date_start = props.order.start_date || undefined;
    }

    if (props.order.no_haggling || !props.order.amount_by_agreement) {
      if (props.order.amount_account !== undefined && props.order.amount_account !== null) {
        offer.value.amount_account = props.order.amount_account;
      }
      if (props.order.amount_account_vat !== undefined && props.order.amount_account_vat !== null) {
        offer.value.amount_account_vat = props.order.amount_account_vat;
      }
      if (props.order.amount_cash !== undefined && props.order.amount_cash !== null) {
        offer.value.amount_cash = props.order.amount_cash;
      }
    }
  }
});

const store = () => {
  if (!offer.value) {
    return;
  }
  if (!props.order?.id) {
    return;
  }
  const offerData: any = {
    order_id: props.order.id,
  };

  if (offer.value.amount_account_vat != null && offer.value.amount_account_vat !== '') {
    offerData.amount_account_vat = offer.value.amount_account_vat;
  }
  if (offer.value.amount_account != null && offer.value.amount_account !== '') {
    offerData.amount_account = offer.value.amount_account;
  }
  if (offer.value.amount_cash != null && offer.value.amount_cash !== '') {
    offerData.amount_cash = offer.value.amount_cash;
  }
  if (offer.value.date_start != null) {
    const d = offer.value.date_start;
    offerData.date_start = typeof d === 'string' ? d : (d && typeof (d as any)?.toISOString === 'function' ? (d as Date).toISOString().slice(0, 10) : String(d));
  }
  if (offer.value.comment != null && offer.value.comment !== '') {
    offerData.comment = offer.value.comment;
  }

  apiFetch(`orders/${props.order.id}/order-offers`, {
    method: "post",
    body: offerData
  }).then(() => {
    dialog.value = false;
    emit('close');
  }).catch((error: any) => {
    if (error?.body?.errors) {
      errors.value = error.body.errors;
    } else {
      errors.value = {};
    }
  });
}

const update = () => {
  if (!offer.value) {
    return;
  }
  if (!props.order?.id || !offer.value?.id) {
    return;
  }
  const offerData: any = {};
  
  if (offer.value.amount_account_vat) {
    offerData.amount_account_vat = offer.value.amount_account_vat;
  }
  if (offer.value.amount_account) {
    offerData.amount_account = offer.value.amount_account;
  }
  if (offer.value.amount_cash) {
    offerData.amount_cash = offer.value.amount_cash;
  }
  if (offer.value.date_start) {
    offerData.date_start = offer.value.date_start;
  }
  if (offer.value.comment) {
    offerData.comment = offer.value.comment;
  }
  
  apiFetch(`orders/${props.order.id}/order-offers/${offer.value.id}`, {
    method: "put",
    body: offerData
  }).then(() => {
    dialog.value = false;
    emit('close');
  }).catch((error) => {
    if (error.body && error.body.errors) {
      errors.value = error.body.errors;
    }
  });
}

const submit = () => {
  errors.value = {};

  if (offer.value.id) {
    update()
  } else {
    store();
  }
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
