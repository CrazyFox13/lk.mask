<template>
  <div>
    <ContentNotFound v-if="offers.length===0"/>
    <div v-else :key="refreshKey">
      <OrderOfferCard
          v-for="offer in offers"
          :key="offer.id"
          :offer="offer"
          class="mb-15"
          @close="loadOffers()"
          @accepted="(offerId) => handleAccepted(offerId)"
          @declined="loadOffers()"
          @reverted="loadOffers()"
          @document-uploaded="loadOffers()"
          @order-in-progress="loadOffers()"
      />
      <el-pagination
          v-model:current-page="page"
          :page-size="TAKE"
          layout="prev, pager, next"
          :total="totalCount"
      />
    </div>
  </div>
</template>

<script setup lang="ts">

import OrderOfferCard from "~/components/OrderOffers/OrderOfferCard.vue";
import {useOrderStore} from "~/stores/order";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import {nextTick} from "vue";
import {storeToRefs} from "pinia";

const {getOffers} = useOrderStore();
const {acceptedOffers} = storeToRefs(useOrderStore());

const route = useRoute();
const orderID = route.params.id;


const offers = ref([]);
const refreshKey = ref(0);

const page = ref(1);
const totalCount = ref(0);
const TAKE = 10;

const loadOffers = async (preserveLocalChanges = true) => {
  try {
    const data = await getOffers(Number(orderID),{statuses:"waiting,accepted,declined"}, page.value, TAKE);
    const serverOffers = data.offers || [];
    
    // Применяем локальные изменения из acceptedOffers к данным с сервера
    // Это гарантирует, что локально принятые предложения сохранят статус 'accepted'
    if (preserveLocalChanges && acceptedOffers.value && acceptedOffers.value.size > 0) {
      serverOffers.forEach((offer: any) => {
        if (offer.id && acceptedOffers.value.has(offer.id)) {
          console.log('Применяем локальный статус accepted к предложению', offer.id);
          offer.status = 'accepted';
        }
      });
    }
    
    offers.value = serverOffers;
    totalCount.value = data.totalCount || 0;
  } catch (error) {
    console.error('Ошибка при загрузке предложений:', error);
    offers.value = [];
    totalCount.value = 0;
  }
}

// Обработчик для события accepted - автоматически обновляем данные с сервера
const handleAccepted = async (offerId?: number) => {
  console.log('handleAccepted вызван, offerId:', offerId);
  console.log('acceptedOffers до обновления:', acceptedOffers.value);
  
  // Обновляем локально статус предложения сразу, чтобы изменения были видны
  if (offerId) {
    const offer = offers.value.find((o: any) => o.id === offerId);
    if (offer) {
      offer.status = 'accepted';
      console.log('Локально обновлен статус предложения', offerId, 'на accepted');
      
      // Сбрасываем статус других предложений на 'waiting'
      offers.value.forEach((o: any) => {
        if (o.id !== offerId && o.status === 'accepted') {
          o.status = 'waiting';
          // Удаляем другие предложения из acceptedOffers
          if (acceptedOffers.value && acceptedOffers.value.delete) {
            acceptedOffers.value.delete(o.id);
          }
        }
      });
    }
  }
  
  // Принудительно обновляем компонент для отображения изменений
  await nextTick();
  refreshKey.value++;
  
  // Обновляем данные с сервера через небольшую задержку
  // Локальные изменения будут сохранены благодаря логике в loadOffers
  setTimeout(async () => {
    console.log('Обновляем данные с сервера, acceptedOffers:', acceptedOffers.value);
    await loadOffers(true); // preserveLocalChanges = true
    console.log('Данные обновлены с сервера, offers:', offers.value);
    console.log('acceptedOffers после обновления:', acceptedOffers.value);
  }, 1500);
}

onBeforeMount(() => {
  loadOffers();
})


watch(page, () => {
  loadOffers();
})
</script>

<style scoped lang="scss">

</style>