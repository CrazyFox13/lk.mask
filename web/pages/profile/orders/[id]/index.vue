<template>
  <OrderViewCard
      :order="order"
      @removed="onRemoved"
      @moderate="onModerate"
      @complete="onComplete"
      @statusChanged="onStatusChanged"
  />
</template>

<script setup lang="ts">
import OrderViewCard from "~/components/Orders/OrderViewCard.vue";
import {apiFetch} from "~/composables/apiFetch";
import {useRoute} from "#imports";

const route = useRoute();
const orderId = Number(route.params.id);

const order = ref();
const data = await apiFetch(`orders/${orderId}`);
order.value = data.order;

const onRemoved = () => {
  order.value.moderation_status = 'removed'
};

const onModerate = (status: string) => {
  order.value.moderation_status = status
};

const onComplete = () => {
  order.value.moderation_status = 'completed'
};

const onStatusChanged = (status: string) => {
  order.value.moderation_status = status
};

</script>

<style scoped lang="scss">

</style>