<template>
  <div class="order-filter-item">
    <nuxt-link :to="`/orders?${query}`" @click="emit('selected')">
      <div class="text-h4" v-html="truncate(value.name,isMobile?120:140)"></div>
    </nuxt-link>
    <div class="flex justify-between mt-10">
      <label>
        Получать E-mail уведомления
      </label>
      <el-switch @change="update()" v-model="value.active_email" :active-value="1" :inactive-value="0"/>
    </div>
    <el-button size="small" link class="text-semibold" @click="confirmDeleteDialog=true">
      <SvgIcon name="trash" class="text-gray"/>&nbsp;
      Удалить
    </el-button>

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
          <div class="text-black modal-form-title m-0 text-center">Вы уверены, что хотите удалить фильтр?</div>
          <div class="confirmation-actions">
            <el-button type="primary" @click="destroy()">Удалить</el-button>
            <el-button type="primary" plain @click="confirmDeleteDialog=false">Отменить</el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, computed, truncate, objToQuery} from "#imports";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";

const props = defineProps(['orderFilter']);
const emit = defineEmits(['deleted', 'selected']);
const value = ref(props.orderFilter);
const confirmDeleteDialog = ref(false);
const {isMobile} = storeToRefs(useDeviceStore())
const update = () => {
  apiFetch(`order-filters/${value.value.id}`, {
    method: "PUT",
    body: value.value
  })
}

const destroy = () => {
  apiFetch(`order-filters/${value.value.id}`, {
    method: "DELETE",
  }).then(() => {
    emit("deleted")
  })
}

const query = computed(() => {
  return objToQuery(JSON.parse(value.value.query));
})
</script>

<style scoped lang="scss">
.order-filter-item {
  padding: 20px 0;
  border-bottom: 1px solid #E8EBF1;
}

.el-button {
  padding-left: 0 !important;
  display: flex;
  align-items: center;
}

a {
  text-decoration: none;
  color: inherit;
}
</style>