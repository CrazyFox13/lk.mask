<template>
  <client-only>
    <el-dialog
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
          <div class="text-black modal-window-title m-0">Мои поиски</div>
        </div>
      </template>

      <div class="order-filters-list">
        <OrderFilterItem
            v-for="(orderFilter,i) in orderFilters"
            :key="orderFilter.id"
            :order-filter="orderFilter"
            @deleted="onDeleted(i)"
            @selected="emit('close')"
        />
      </div>
    </el-dialog>
  </client-only>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, nextTick, onMounted, useAsyncData} from "#imports";
import OrderFilterItem from "~/components/Orders/OrderFilter/OrderFilterItem.vue";

const dialog = ref(false);
const {isMobile} = storeToRefs(useDeviceStore());
const emit = defineEmits(['close']);
const orderFilters = ref<any[]>([]);

const handleClose = () => {
  dialog.value = false;
  nextTick(() => {
    emit('close')
  })
}

onMounted(() => {
  dialog.value = true;
})


const getOrderFilters = async () => {
  const data = await apiFetch(`order-filters`);
  orderFilters.value = data.orderFilters;
}
useAsyncData(() => getOrderFilters());

const onDeleted = (i: number) => {
  orderFilters.value.splice(i, 1);
}
</script>

<style scoped lang="scss">
.modal-window-title {
  text-align: center;
  @media (min-width: 992px) {
    text-align: left;
  }
}

.order-filters-list {
  @media (min-width: 992px) {
    max-height: 506px;
    overflow-y: auto;
    padding-right: 30px;
  }
}
</style>