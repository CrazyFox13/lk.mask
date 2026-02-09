<template>
  <div>
    <ContentNotFound v-if="notifications.length===0"/>
    <div v-else>
      <div class="actions">
        <el-button link class="read-all mr-4" @click="readAll()">
          <SvgIcon name="eye" class="mr-2"/>
          <span class="hidden-sm-and-down">Прочитать все</span>
        </el-button>
        <nuxt-link class="el-link read-all" to="/profile/settings?back=notifications">
          <SvgIcon name="settings" class="mr-2"/>
          <span class="hidden-sm-and-down">Настройки</span>
        </nuxt-link>
      </div>
      <el-card class="mb-40">
        <NotificationItem
            v-for="notification in notifications"
            :notification="notification" :key="notification.id"
            class="notification-item"
            ref="notificationItem"
        />
      </el-card>
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
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch} from "#imports";
import {useAuthStore} from "~/stores/user";
import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import NotificationItem from "~/components/Profile/Notifications/NotificationItem.vue";
import {useBadgeStore} from "~/stores/badges";
import {storeToRefs} from "pinia";

const {user} = useAuthStore();
const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const notifications = ref<any[]>([]);
const TAKE = 8;

const badgeStore = useBadgeStore();
const {removeBadge} = badgeStore;
const {badges} = storeToRefs(badgeStore);
const notificationItem = ref(null);

const getOrders = async () => {
  loading.value = true;
  const data = await apiFetch(`company-notifications?page=${page.value}&take=${TAKE}`, {}, true);
  notifications.value = data.companyNotifications;
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getOrders());

watch(page, () => {
  getOrders();
});

const readAll = () => {
  apiFetch(`company-notifications/read-all`, {
    method: "POST"
  }).then(() => {
    notificationItem.value.forEach(i => i.read())
    removeBadge("total_badges_value", badges.value.total_badges_value);
    removeBadge("notifications_badges_value", badges.value.notifications_badges_value);
  })
}
</script>

<style scoped lang="scss">
.notification-item {
  border-bottom: 1px solid #E8EBF1;

  &:last-of-type {
    border-bottom: none;
  }
}

.actions {
  display: inline-flex;
  justify-content: flex-end;
  position: absolute;
  right: 0;
  top: -66px;
}

</style>