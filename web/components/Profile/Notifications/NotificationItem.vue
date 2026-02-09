<template>
  <nuxt-link :to="routeLink" class="notification" @click="onClick()">
    <div class="text-h5 flex align-items-center" style="margin-bottom: 6px">
      <span class="is-new" v-if="!value.viewed_at"/>
      {{ value.subject }}
    </div>
    <p class="mt-0" style="margin-bottom: 4px" v-html="value.text"></p>
    <p class="m-0 flex align-items-center text-gray text-sub-subtitle">
      <SvgIcon name="clock" class="mr-2"/>
      {{ ruMoment(value.created_at).fromNow() }}
    </p>
  </nuxt-link>
</template>

<script setup lang="ts">
import {emitter, ruMoment} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {useBadgeStore} from "~/stores/badges";
import moment from 'moment';

const {removeBadge} = useBadgeStore();

const props = defineProps(['notification']);

const value = ref(props.notification);

const read = () => {
  value.value.viewed_at = moment().toDate();
}

const onClick = () => {
  apiFetch(`company-notifications/${props.notification.id}/read`, {
    method: "POST"
  }).then(() => {
    read();
    removeBadge("total_badges_value", 1);
    removeBadge("notifications_badges_value", 1);
  })
}

const data = value.value.data;
const routeLink = computed(() => {
  // Если есть order_id, всегда открываем заявку, независимо от типа уведомления
  if (data.order_id) {
    return `/profile/orders/${data.order_id}`;
  }
  
  // Для уведомлений без order_id используем стандартную логику
  switch (data.key) {
    case "company_moderation_passed":
      return `/profile/company`;
    case "company_moderation_failed":
      return `/profile/company`;
    case "rating_increased":
      return `/profile/passport`;
    case "rating_decreased":
      return `/profile/passport`;
    case "new_message":
      return `/profile/reports`;
    case "report_completed":
      return `/profile/reports`;
    case "report_created":
      return `/profile/reports`;
    case "recommendation_created":
      return `/profile/recommendations`;
    default:
      if (data.push_notification_id) {
        return `/profile/notifications/${data.push_notification_id}/material`
      }
      return ``;
  }
});

defineExpose({read})

</script>

<style scoped lang="scss">
.notification {
  padding: 16px 0;
  display: block;
  text-decoration: none;
  color: inherit;;

  @media (min-width: 992px) {
    padding: 24px 0;
  }

  .is-new {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #F92609;
    margin-right: 8px;
  }
}
</style>