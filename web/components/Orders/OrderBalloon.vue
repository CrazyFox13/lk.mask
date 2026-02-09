<template>
  <div>
    <div class="flex justify-between align-items-start">
      <div class="text-h5 mb-10">{{ order.title }}</div>
      <FavoriteButton class="ml-3" no-border="true" :order="order"/>
    </div>
    <div class="mb-2 flex text-sub-subtitle prices-list">
      <div class="price text-black" v-if="order.amount_account_vat">
        <SvgIcon name="vat"/>
        {{ formatPrice(order.amount_account_vat) }}
      </div>
      <div class="price text-black" v-if="order.amount_account">
        <SvgIcon name="no_vat"/>
        {{ formatPrice(order.amount_account) }}
      </div>
      <div class="price text-black" v-if="order.amount_cash">
        <SvgIcon name="payment"/>
        {{ formatPrice(order.amount_cash) }}
      </div>
      <div class="price text-black" v-if="order.amount_by_agreement">
        <SvgIcon name="payment"/>
        По договорённости
      </div>
    </div>
    <div class="flex align-items-center text-sub-subtitle subtext-dates mb-2">
      <SvgIcon name="clock" class="mr-2"/>
      c {{ moment(order.start_date).format("DD.MM.YY") }} на {{
        shiftsCount(order.start_date, order.finish_date)
      }}
      смены
    </div>
    <div class="flex align-items-center subtext-location mb-2" v-if="order.start_address && order.start_address.city">
      <SvgIcon name="location" class="mr-2"/>
      {{ order.start_address.city.name }}
    </div>

    <div v-if="order.company" class="flex align-items-center justify-between mb-2">
      <p class="m-0" v-bind:class="{'blur':!user}">
        {{ order.company.title }}
      </p>
      <div class="flex">
        <RatingStars class="mr-3" :rating="order.company.rating"/>
        <LikesCount class="mr-2" :likes="order.company.approved_recommendations_count"/>
        <DislikesCount :dislikes="order.company.active_reports_count"/>
      </div>
    </div>
    <div v-else-if="order.user" class="flex align-items-center justify-between mb-2">
      <p class="text-black" v-bind:class="{'blur':!user}">
        {{ order.user.name }} {{ order.user.surname }}
      </p>
      <div class="flex">
        <RatingStars class="mr-3" :rating="order.user.rating"/>
        <LikesCount class="mr-2" :likes="order.user.approved_recommendations_count"/>
        <DislikesCount :dislikes="order.user.active_reports_count"/>
      </div>
    </div>
    <div class="order-offers" v-if="order.offers_count>0">
      <div class="order-offers-item">
        <SvgIcon width="18px" name="offers">
          <template #badge>
            <BadgeLabel :value="order.new_offers_count"/>
          </template>
        </SvgIcon>
        <div>
          {{ order.offers_count }} Предложений
        </div>
      </div>
      <div class="order-offers-item" v-if="order.company_offer">
        <SvgIcon class="text-orange" width="18px" name="message-dollar"/>
        <div>
          Ваше
          {{ formatPrice(order.company_offer.amount_account_vat || order.company_offer.amount_account || order.company_offer.amount_cash) }}
        </div>
      </div>
    </div>
    <div class="flex justify-between">
      <a :href="`/orders/${order.id}`" class="el-link el-link--primary">Подробнее</a>
      <div class="flex text-secondary text-gray">
        {{ moment(order.publish_date).format("DD.MM.YY") }}
        <div class="ml-3">
          <SvgIcon name="eye"/>
          {{ order.views_count }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import RatingStars from "~/components/Common/RatingStars.vue";
import LikesCount from "~/components/Common/LikesCount.vue";
import DislikesCount from "~/components/Common/DislikesCount.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";
import moment from "moment";
import {useOrderStore} from "~/stores/order";
import FavoriteButton from "~/components/Orders/FavoriteButton.vue";
import {formatPrice} from "#imports";
import BadgeLabel from "~/components/Common/BadgeLabel.vue";

const props = defineProps(['order']);

const authStore = useAuthStore()
const {user} = storeToRefs(authStore);
const {shiftsCount} = useOrderStore();
</script>

<style scoped lang="scss">
.prices-list {
  flex-wrap: wrap;
  row-gap: 8px;

  .price {
    font-weight: 400;
    font-size: 12px;
    line-height: 16px;
    display: flex;
    align-items: center;
    margin-right: 20px;

    &:last-of-type {
      margin-right: 0;
    }

    span {
      margin-right: 3px;
      display: flex;
      align-items: center;
    }
  }
}

.order-offers {
  margin-top: 5px;
  margin-bottom: 5px;
  display: flex;
  align-items: center;
  column-gap: 15px;

  &-item {
    display: flex;
    align-items: center;
    column-gap: 5px;

  }
}
</style>