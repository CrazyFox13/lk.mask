<template>
  <client-only>
    <el-dialog
        v-model="dialog"
        :fullscreen="isMobile"
        width="768"
        :before-close="handleClose"
        :show-close="false"
    >
      <template #header="{close}">
        <div class="dialog-head">
          <div class="text-black text-h3 text-left hidden-sm-and-down">Предложения</div>
          <div class="text-black text-h3 text-center hidden-md-and-up">Предложения</div>
          <el-button class="close-btn text-black" circle text @click="close">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>

      <div class="offers-list">
        <OrderOfferCard
            v-for="offer in offers"
            :key="offer.id"
            :offer="offer"
            @close="emit('close')"
            @accepted="(offerId) => handleAccepted(offerId)"
            @declined="getData()"
            @reverted="getData()"
            class="mb-15"
        />
      </div>
    </el-dialog>
  </client-only>
</template>

<script lang="ts" setup>
import SvgIcon from "../SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {useOrderStore} from "~/stores/order";
import OrderOfferCard from "~/components/OrderOffers/OrderOfferCard.vue";

const props = defineProps(['order']);
const emit = defineEmits(['close', 'reload']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);

const {getOffers} = useOrderStore();
const handleClose = () => {
  dialog.value = false;
  emit('close')
}

const offers = ref([])

onBeforeMount(() => {
  getData();
})

const getData = ()=>{
  getOffers(props.order.id, {statuses: "waiting,accepted,declined"}, 1, -1).then(data => {
    offers.value = data.offers;
  });
}

// Обработчик для события accepted - обновляем локально статус в массиве offers
const handleAccepted = (offerId?: number) => {
  // Обновляем статус локально в массиве offers, чтобы не терять localStatus в компоненте
  if (offerId) {
    const offer = offers.value.find((o: any) => o.id === offerId);
    if (offer) {
      offer.status = 'accepted';
      // Также обновляем другие offers - сбрасываем их статус на 'waiting', если они были 'accepted'
      offers.value.forEach((o: any) => {
        if (o.id !== offerId && o.status === 'accepted') {
          o.status = 'waiting';
        }
      });
    }
  }
  // Не обновляем данные с сервера автоматически - пусть пользователь обновит страницу или данные обновятся при следующем действии
  // Это предотвращает потерю localStatus при пересоздании компонентов
}
</script>

<style scoped lang="scss">
.offers-list {
  max-height: 555px;
  overflow-y: auto;
  padding: 24px 12px;
}
</style>