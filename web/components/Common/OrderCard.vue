<template>
  <NuxtLink class="order" :to="to?to:(employee?`/profile/orders/${order.id}`:`/orders/${order.id}`)">
    <el-card class="hoverable" v-bind:class="{'viewed':order.is_viewed, 'closed':isClosed}">
      <div v-if="showStatusBadge" class="status-badge">
        <OrderStatus :status="order.moderation_status"/>
      </div>
      <div class="flex align-items-center mb-1">
        <OrderStatus
            v-if="employee" class="hidden-md-and-up mr-3" :status="order.moderation_status"/>
        <span v-if="showAuthor"
              class="hidden-md-and-up text-gray text-sub-subtitle">
          {{ order.user?.name }} {{ order.user?.surname }}
        </span>
      </div>
      <el-row>
        <el-col :span="24" :md="short?24:16" class="col-order">
          <div class="flex">
            <el-skeleton
                class="hidden-sm-and-down"
                v-if="showLogo && !props.order.vehicle_type.group.logo">
              <template #template>
                <el-skeleton-item variant="image" style="width: 56px; height: 56px"/>
              </template>
            </el-skeleton>
            <img
                v-if="showLogo && order.vehicle_type.group.logo"
                :src="order.vehicle_type.group.logo" :alt="order.vehicle_type.group.title"
                class="hidden-sm-and-down vehicle-logo"
            />

            <div class="main-info">
              <div class="text-black text-h4">{{ order.title }}</div>
              <p class="text-gray" v-html="truncate(order.description, 70)"></p>
              <OrderPrices
                  v-if="!isSupplierCompany"
                  class="mb-2"
                  :amount_account_vat="order.amount_account_vat"
                  :amount_account="order.amount_account"
                  :amount_cash="order.amount_cash"
                  :amount_by_agreement="order.amount_by_agreement"
              />
            </div>
            <OrderCardHoverButtons class="hidden-md-and-up" :order="order"/>
          </div>
          <div class="flex text-sub-subtitle align-items-center flex-wrap subtext">
            <OrderDuration v-bind:class="{'order-dates':!employee}" :order="order"/>
            <div class="flex align-items-center subtext-location"
                 v-if="order.start_address && order.start_address.city">
              <SvgIcon name="location" class="mr-2"/>
              {{ order.start_address.city.name }}
            </div>
            <div class="flex text-secondary text-gray align-items-center" v-if="order.publish_date && !isSupplierCompany">
              {{ moment(order.publish_date).format("DD.MM.YY") }}
              <div class="ml-3 flex align-items-center">
                <SvgIcon name="eye"/>
                {{ order.views_count }}
              </div>
            </div>
            <span
                v-if="showAuthor"
                class="hidden-sm-and-down ml-auto text-gray text-sub-subtitle"
            >{{ order.user?.name }} {{ order.user?.surname }}</span>
          </div>
          <div class="order-offers" v-bind:class="{'offset':!short}" v-if="showOffers && !isSupplierCompany">
            <OrderOffersCount class="order-offers-item" :order="order"/>
            <div class="order-offers-item" v-if="order.company_offer">
              <SvgIcon class="text-orange mr-2" width="18px" name="message-dollar"/>
              <div>
                Ваше
                {{
                  formatPrice(order.company_offer.amount_account_vat || order.company_offer.amount_account || order.company_offer.amount_cash)
                }}
              </div>
            </div>
          </div>
        </el-col>
        <el-col :span="24" :md="8" class="col-company" v-if="!short && !isCustomerCompany && !isSupplierCompany">
          <div class="flex justify-between">
            <div class="w-100">
              <div v-if="order.company">
                <el-divider class="hidden-md-and-up"/>
                <div class="text-black text-h6 title-with-icon" v-bind:class="{'blur':!user}">
                  <SvgIcon name="verification" v-if="order.company.verified"/>
                  {{ order.company.title }}
                </div>
                <p v-if="order.user" class="text-subtitle text-gray" v-bind:class="{'blur':!user}">
                  {{ order.user.name }} {{ order.user.surname }}
                </p>
                <div class="flex">
                  <RatingStars class="mr-3" :rating="order.company.rating"/>
                  <LikesCount class="mr-2" :likes="order.company.approved_recommendations_count"/>
                  <DislikesCount :dislikes="order.company.active_reports_count"/>
                </div>
              </div>
              <div v-else-if="order.user">
                <el-divider class="hidden-md-and-up"/>
                <div class="text-black text-h4" v-bind:class="{'blur':!user}">
                  {{ order.user.name }} {{ order.user.surname }}
                </div>
                <div class="flex">
                  <RatingStars class="mr-3" :rating="order.user.rating"/>
                  <LikesCount class="mr-2" :likes="order.user.approved_recommendations_count"/>
                  <DislikesCount :dislikes="order.user.active_reports_count"/>
                </div>
              </div>
              <el-button
                  v-if="order.user?.phone"
                  :type="isCompleted || phone?'info':'primary'"
                  plain
                  class="show-phone-button"
                  :data-href="`tel:${order.user?.phone}`"
                  @click.prevent="showPhone">
                {{
                  isCompleted ? 'Завершённая' : phone ? formatPhone(order.user.phone) : 'Показать телефон'
                }}
              </el-button>
            </div>
            <OrderCardHoverButtons class="button-bar-md hidden-sm-and-down" :order="order"/>
          </div>
        </el-col>
      </el-row>
      <OrderCardHoverButtons
          v-if="short && !employee"
          class="button-bar-md hidden-sm-and-down button-bar-abs"
          :order="order"
      />
      <div v-if="short">
        <OrderStatus
            v-if="user && order.company_id === user.company_id"
            class="button-bar-abs hidden-sm-and-down"
            :status="order.moderation_status"/>
        <OrderOfferStatus
            v-if="user && order.company_offer?.company_id === user.company_id"
            class="button-bar-abs hidden-sm-and-down"
            :status="order.company_offer.status"/>

      </div>
    </el-card>
  </NuxtLink>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import RatingStars from "~/components/Common/RatingStars.vue";
import LikesCount from "~/components/Common/LikesCount.vue";
import DislikesCount from "~/components/Common/DislikesCount.vue";
import moment from "moment";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {computed, ref, useRouter} from "#imports";
import OrderPrices from "~/components/Orders/OrderPrices.vue";
import OrderDuration from "~/components/Orders/OrderDuration.vue";
import {truncate, formatPhone} from "#imports";
import OrderCardHoverButtons from "~/components/Orders/OrderCardHoverButtons.vue";
import OrderStatus from "~/components/Orders/OrderStatus.vue";
import {formatPrice} from "#imports";
import BadgeLabel from "~/components/Common/BadgeLabel.vue";
import OrderOffersCount from "~/components/Orders/OrderOffersCount.vue";
import OrderOfferStatus from "~/components/Orders/OrderOfferStatus.vue";

const props = defineProps(['order', 'short', 'employee', 'showAuthor', 'to', 'showStatusBadge']);

const authStore = useAuthStore()
const {user} = storeToRefs(authStore);
const router = useRouter();
const phone = ref(false);

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

// Проверяем, является ли компания автора заявки типом "Поставщик" (не "Заказчик")
const isSupplierCompany = computed(() => {
  return props.order?.company?.company_type_id && props.order.company.company_type_id !== CUSTOMER_COMPANY_TYPE_ID;
});

const showLogo = computed(() => {
  if (props.employee) return false;
  return props.order.vehicle_type && props.order.vehicle_type.group;
})

const isCompleted = computed(() => {
  return props.order?.moderation_status === 'completed'
});

const isClosed = computed(() => {
  return props.order?.moderation_status === 'removed' || isCompleted.value
})


const showPhone = (e: any) => {
  if (isClosed.value) return;
  if (!user.value) {
    try {
      (window as any).ym(65983300, 'reachGoal', 'show_phone_guest')
    } catch (e) {
      console.log("ym", 'show_phone_guest', e)
    }
    router.push('/auth/sign-up');
    return;
  }
  if(!user.value.allowed_to_show_contacts){
    router.push('/profile');
    return;
  }
  if (phone.value) {
    window.location.href = e.target.closest('button').dataset.href;
    try {
      (window as any).ym(65983300, 'reachGoal', 'click_phone')
    } catch (e) {
      console.log("ym", 'click_phone', e)
    }
    return;
  }
  try {
    (window as any).ym(65983300, 'reachGoal', 'show_phone')
  } catch (e) {
    console.log('ym', 'show_phone', e)
  }
  phone.value = true;
}


const showOffers = computed(() => {
  if (props.order.offers_count === 0) {
    return false;
  }

  if (props.short) {
    return props.order.company_id === user.value?.company_id;
  }

  return true;
})
</script>

<style scoped lang="scss">
.order {
  margin-bottom: 20px;
  display: block;
  text-decoration: none;
  position: relative;
}

.text-h4 {
  margin-top: 0;
  margin-bottom: 4px;
}

p {
  margin-top: 0;
  margin-bottom: 10px;
}

.el-skeleton, .vehicle-logo {
  width: auto;
  margin-right: 20px;
}

.vehicle-logo {
  height: 56px;
}

.main-info {
  margin-right: 20px;
  width: 100%;

  .text-secondary {
    font-weight: 400;
    font-size: 12px;
    line-height: 16px;
  }
}

.order-dates {
  @media (min-width: 992px) {
    margin-left: 75px;
  }
}

.subtext {
  & > * {
    margin-right: 20px;

    &:last-of-type {
      margin-right: 0;
    }
  }

  .subtext-location {
    flex: 1;

    span {
      display: flex;
      align-items: center;
    }

    @media (min-width: 992px) {
      flex: initial;
    }
  }
}

.w-100 {
  width: 100%;
}

.show-phone-button {
  margin-top: 19px;
  width: 100%;
  padding: 11px 20px;
}

@media (min-width: 992px) {
  .col-company {
    padding-left: 15px;
    border-left: 1px solid #E8EBF1;
  }

  .col-order {
    padding-right: 15px;
  }

  .show-phone-button {
    max-width: 150px;
    font-size: 13px;
    padding: 5px 20px !important;
  }
}

.el-divider--horizontal {
  margin: 16px 0;
}

.blur {
  filter: blur(3px);
}

@media (min-width: 992px) {
  .button-bar-md {
    visibility: hidden;
    //display: none;
  }

  .el-card:hover {
    .button-bar-md {
      visibility: visible;
      //display: flex;
    }
  }
}

.button-bar-abs {
  position: absolute;
  right: 20px;
  top: 20px;
}

.status-badge {
  position: absolute;
  right: 20px;
  top: 20px;
  padding: 3px 10px;
  border-radius: 6px;
  border: none;
  background: transparent;
  font-size: 13px;
  line-height: 16px;
  color: #222222;
}

.viewed {
  background: rgba(233, 235, 239, .8);
  box-shadow: none !important;

  .text-h4 {
    color: rgba(34, 34, 34, 0.60);
  }
}

.closed {
  opacity: 0.6;

  .text-h4 {
    text-decoration: line-through;
  }
}

.order-offers {
  margin-top: 5px;
  display: flex;
  align-items: center;
  column-gap: 15px;


  @media (min-width: 992px) {
    &.offset {
      padding-left: 74px;
    }
  }

  &-item {
    display: flex;
    align-items: center;

  }
}

</style>