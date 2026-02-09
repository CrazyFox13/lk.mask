<template>
  <div class="page">
    <div class="container">
      <div class="flex gap-2 items-center el-breadcrumb">
        <nuxt-link class="is-link" :to="{ path: '/' }">Главная</nuxt-link>
        <el-icon class="separator"><ArrowRight></ArrowRight></el-icon>
        <nuxt-link class="is-link" :to="{ path: '/orders' }">Список заявок</nuxt-link>
        <el-icon class="separator"><ArrowRight></ArrowRight></el-icon>
        <nuxt-link class="is-link inactive">{{ order.title }}</nuxt-link>
      </div>
      <el-row>
        <el-col :span="24" :md="16">
          <OrderViewCard
              @report="reportDialog=true"
              @moderate="refreshOrder()"
              @removed="refreshOrder()"
              @complete="refreshOrder()"
              @statusChanged="refreshOrder()"
              :order="order"
          />
          <Adv key="orders_item" width="765" height="130" :banner="findBanner(banners, 'orders_item')"
               class="hidden-sm-and-down mt-20"/>

          <YaAdv ya-id="yandex_rtb_R-A-6812315-6" block-id="R-A-6812315-6" :width="765" :height="130"
                 class="hidden-sm-and-down mt-20"/>

        </el-col>
        <el-col :span="24" :md="8" v-if="!isClosed">
          <div class="sticky-contacts" v-if="order.user">
            <CompanyInfo :order="order"/>
            <CompanyOffer
                v-if="order.company_offer"
                class="mt-20"
                :offer="order.company_offer"
                @deleted="refreshOrder()"
            />
            <OrderOffers
                v-if="isMineOrder && order.offers_count"
                class="mt-20"
                :order="order"
                @open="showOffersModal=true"
            />
          </div>
        </el-col>
      </el-row>
    </div>
    <SendClaimDialog v-if="reportDialog" @close="reportDialog=false" :order="order"/>
    <CreateOrderOffer v-if="offerDialog" @close="offerDialog=false" :order="order"/>
    <OrderOffersModal v-if="showOffersModal" @close="showOffersModal=false" :order="order" @reload="refreshOrder()"/>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, computed, emitter, findBanner, ref, useHead, useRoute} from "#imports";
import {ArrowRight} from '@element-plus/icons-vue'
import CompanyInfo from "~/components/Orders/CompanyInfo.vue";
import SendClaimDialog from "~/components/Orders/SendClaimDialog.vue";
import OrderViewCard from "~/components/Orders/OrderViewCard.vue";
import Adv from "~/components/Adv.vue";
import YaAdv from "~/components/Adv/YaAdv.vue";
import CreateOrderOffer from "~/components/Orders/CreateOrderOffer.vue";
import CompanyOffer from "~/components/Orders/CompanyOffer.vue";
import OrderOffers from "~/components/Orders/OrderOffers.vue";
import OrderOffersModal from "~/components/Orders/OrderOffersModal.vue";
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";
import moment from "moment/moment";

definePageMeta({
  middleware: 'block-customer-orders'
});

const route = useRoute();
const orderId = Number(route.params.id);
const getOrder = async () => {
  const r = await apiFetch(`orders/${orderId}`);
  return r.order;
}

const authStore = useAuthStore();
const {user} = storeToRefs(authStore)

const initOrder = await getOrder();

const isMineOrder = computed(() => {
  return initOrder.company_id === user.value?.company_id;
})

const order = ref(initOrder)

const reportDialog = ref(false);
const showOffersModal = ref(false);

const {banners} = await apiFetch(`adv?places=orders_item&vehicle_types_id=${order.value.vehicle_type_id}`);

const isClosed = computed(() => {
  return order.value.moderation_status === 'removed' || order.value.moderation_status === 'completed'
});

const offerDialog = ref(false);
emitter.on("offer-modal", () => {
  offerDialog.value = true;
});

const refreshOrder = async () => {
  order.value = await getOrder();
}


useHead(() => ({
  title: `${order.value?.title || ""} c ${moment(order.value.start_date).format("DD.MM.YYYY HH:mm")} заказ от ${order.value?.company?.title}`,
  meta: [
    {
      name: 'description',
      content: order.value?.description || "",
    },
  ],
}))

</script>

<style scoped lang="scss">

.el-col {
  margin-top: 20px;
  @media (min-width: 992px) {
    margin-top: 0;
    &:first-of-type {
      padding-right: 20px;
    }
    &:last-of-type {
      padding-left: 20px;
    }

  }
}

.sticky-contacts {
  position: sticky;
  top: 30px;
}

.card-offset {
  margin-top: 20px;
  @media (min-width: 992px) {
    margin-top: 30px;
  }
}
</style>