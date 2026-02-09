<template>
  <div class="page">
    <div class="container">
      <h1 class="text-h2 mb-40" v-if="showContent && showTitle">
        <el-button v-if="isMobile" @click="onBack()" type="info" class="mr-2 back-btn">
          <SvgIcon name="back"/>
        </el-button>
        {{ title }}
      </h1>
      <el-row>
        <el-col :span="24" :md="6" class="menu-col" v-if="showMenu">
          <ProfileMenu :current-tab="currentTab" :items="items.filter(x=>!x.hidden)" @go="view = 'content'"/>
        </el-col>
        <el-col :span="24" :md="18" class="content-col" v-if="showContent">
          <NuxtPage/>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import ProfileMenu from "~/components/Profile/ProfileMenu.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {computed, nextTick, useRoute, useRouter, emitter} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {useBadgeStore} from "~/stores/badges";
import {useAuthStore} from "~/stores/user";

const {user} = storeToRefs(useAuthStore());

definePageMeta({
  middleware: ["auth"]
});


export interface MenuItem {
  label: string
  icon: string
  url?: string
  pattern: RegExp
  handler?: Function
  key: string
  hidden?: boolean
}


const {isMobile} = storeToRefs(useDeviceStore());

const showMenu = computed(() => {
  return !isMobile.value || view.value === 'menu';
});

const showContent = computed(() => {
  return !isMobile.value || view.value === 'content';
});

const {badges} = storeToRefs(useBadgeStore());
const {logout} = useAuthStore();
const router = useRouter();

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

const items = computed(() => {
  return [
    {
      label: 'Персональные данные',
      icon: 'user',
      url: '/profile',
      pattern: /^\/profile$/,
      key: 'default',
      title: "Настройки профиля"
    },
    {label: 'Компания', icon: 'company', url: '/profile/company', pattern: /^\/profile\/company$/, key: 'company'},
    {
      label: 'Паспорт компании',
      icon: 'passport',
      url: '/profile/passport',
      pattern: /^\/profile\/passport$/,
      key: 'passport'
    },
    {
      label: 'Портфолио',
      icon: 'images',
      url: '/profile/portfolio',
      pattern: /^\/profile\/portfolio(?:\/.*)?$/,
      key: 'portfolio',
      hidden: isCustomerCompany.value, // Скрываем для компаний типа "Заказчик"
    },
    {
      label: 'Сотрудники',
      icon: 'users',
      url: '/profile/employees',
      pattern: /^\/profile\/employees(?:\/.*)?$/,
      key: 'employees'
    },
    {
      label: 'Уведомления',
      icon: 'bell',
      url: '/profile/notifications',
      pattern: /^\/profile\/notifications$/,
      key: 'notifications',
      badge: badges.value.notifications_badges_value
    },
    {
      label: 'Заявки',
      icon: 'orders',
      url: '/profile/orders',
      pattern: /^\/profile\/orders(?:\/.*)?$/,
      key: 'orders'
    },
    {
      label: 'Предложения',
      icon: '',
      pattern: /^\/profile\/orders\/\d+\/offers$/,
      key: 'offers',
      hidden: true
    },
    {
      label: 'Работы',
      icon: 'offers',
      url: '/profile/offers',
      pattern: /^\/profile\/offers(?:\/.*)?$/,
      key: 'offer-jobs',
      hidden: isCustomerCompany.value, // Скрываем для компаний типа "Заказчик"
    },
    {
      label: 'Претензии',
      icon: 'dislike',
      url: '/profile/reports',
      pattern: /^\/profile\/reports$/,
      key: 'reports',
      badge: badges.value.reports_badges_value
    },
    {
      label: 'Рекомендации',
      icon: 'like',
      url: '/profile/recommendations',
      pattern: /^\/profile\/recommendations(?:\/.*)?$/,
      key: 'recommendations',
      badge: badges.value.recommendations_badges_value,
      hidden: isCustomerCompany.value, // Скрываем для компаний типа "Заказчик"
    },
    {
      label: 'Настройки',
      icon: 'settings',
      url: '/profile/settings',
      pattern: /^\/profile\/settings$/,
      key: 'settings'
    },
    {
      label: 'Выйти',
      icon: 'log-out',
      handler: () => {
        router.push('/').then(() => {
          logout();
        })
      },
      pattern: /^\/logout$/,
      key: 'logout'
    },
  ]
});
const route = useRoute();
const currentTab = computed(() => {
  return [...items.value].reverse().find((i: MenuItem) => route.path.match(i.pattern));
});

const exactMenuOpened = computed(() => {
  return currentTab.value?.url === route.path;
})

const view = ref<'menu' | 'content'>(exactMenuOpened.value ? 'menu' : 'content');

const showTitle = computed(() => {
  const hide = isMobile.value && !exactMenuOpened.value;
  return !hide;
});

const title = computed(() => {
  return currentTab.value?.title || currentTab.value?.label;
})

emitter.on('mobile-move', () => {
  view.value = 'menu'
});

const onBack = () => {
  const back = route.query.back;
  if (back) {
    view.value = 'content'
    router.push(`/profile/${back}`)
  } else {
    view.value = 'menu'
  }
}

</script>

<style scoped lang="scss">
@media (min-width: 992px) {
  .menu-col {
    padding-right: 20px
  }

  .content-col {
    padding-left: 20px
  }
}

.back-btn {
  border-radius: 8px;
  padding: 7px 7px;
}
</style>