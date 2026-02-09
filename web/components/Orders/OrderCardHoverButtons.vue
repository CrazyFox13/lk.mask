<template>
  <div class="button-bar">
    <el-tooltip
        class="box-item"
        effect="dark"
        content="Добавить в избранное"
        placement="top-start"
    >
      <FavoriteButton :order="order"/>
    </el-tooltip>
    <SvgIcon style="  visibility: visible;" v-if="['completed','removed'].includes(order.moderation_status)" name="lock"/>
    <el-tooltip
        v-else
        class="box-item"
        effect="dark"
        content="Поделиться"
        placement="top-start"
    >
      <SharePanel :link="`https://astt.su/orders/${order.id}`" :text="shareText">
        <template #default>
          <el-button circle>
            <SvgIcon class="text-gray" name="share"/>
          </el-button>
        </template>
      </SharePanel>
    </el-tooltip>
  </div>
</template>

<script setup lang="ts">
import FavoriteButton from "../Orders/FavoriteButton.vue";
import SvgIcon from "../SvgIcon.vue";
import SharePanel from "~/components/Common/SharePanel.vue";
import {computed, formatPrice} from "#imports";

const props = defineProps(['order']);

const shareText = computed(() => {
  const order = props.order;
  const city = order.start_address?.city?.name;
  const price = [
    order.amount_by_agreement ? 'По договорённости' : '',
    order.amount_cash > 0 ? `${formatPrice(order.amount_cash)} нал.` : '',
    order.amount_account > 0 ? `${formatPrice(order.amount_account)} без НДС` : '',
    order.amount_account_vat > 0 ? `${formatPrice(order.amount_account_vat)} НДС` : '',
  ];
  const priceStr = price.filter(s => s).join(", ");
  return `🔥 Новая заявка на ${order.title}.
${city ? `📍 ${city}` : ''}
💰 ${priceStr}

👉🏻 https://astt.su/orders/${order.id} 👈🏻`
});
</script>

<style scoped lang="scss">
.button-bar {
  display: flex;
  flex-direction: column;
  align-items: center;

  .el-button {
    width: 24px;
    height: 24px;
    border: 1px solid #F3F5F9;
    border-radius: 5px;
    margin-left: 0;
    padding: 0;
    background: inherit;

    @media (min-width: 992px) {
      width: 28px;
      height: 28px;
    }

    &:first-of-type {
      margin-bottom: 4px;
    }
  }
}
</style>