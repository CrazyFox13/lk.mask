<template>
  <div class="page">
    <div class="container">
      <div class="text-h3 mb-30" v-if="isMobile && showContent">
        <el-button v-if="showContent" @click="view='menu'" type="info" class="mr-2 back-btn">
          <SvgIcon name="back"/>
        </el-button>
        {{ currentTab?.title }}
      </div>
      <h1 class="text-h2 mb-40" v-else-if="showTitle">
        Часто задаваемые вопросы
      </h1>
      <el-row>
        <el-col :span="24" :md="6" class="menu-col" v-if="showMenu">
          <HelpMenu :current-tab="currentTab" :items="items" @go="view='content'"/>
        </el-col>
        <el-col :span="24" :md="18" class="content-col" v-if="showContent">
          <NuxtPage :current-tab="currentTab"/>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {apiFetch, computed, useRoute, useRouter} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {useBadgeStore} from "~/stores/badges";
import {useAuthStore} from "~/stores/user";
import HelpMenu from "~/components/Help/HelpMenu.vue";


export interface IMaterialPage {
  title: string
  path: string
}


const {isMobile} = storeToRefs(useDeviceStore());

const showMenu = computed(() => {
  return !isMobile.value || view.value === 'menu';
});

const showContent = computed(() => {
  return !isMobile.value || view.value === 'content';
});

const {badges} = useBadgeStore();
const {logout} = useAuthStore();
const router = useRouter();


const items = [
  {'path': '/faq', 'title': 'О сервисе'},
  {'path': '/faq/get-started', 'title': 'Начало работы'},
  {'path': '/faq/to-customers', 'title': 'Заказчикам'},
  {'path': '/faq/rating', 'title': 'Рейтинги',}
];

const route = useRoute();
const currentTab = computed(() => {
  return [...items].reverse().find((i: IMaterialPage) => route.path.includes(String(i.path)));
});

const exactMenuOpened = computed(() => {
  return currentTab.value?.path === route.path;
})

const view = ref<'menu' | 'content'>(exactMenuOpened.value ? 'menu' : 'content');

const showTitle = computed(() => {
  const hide = isMobile.value && !exactMenuOpened.value;
  return !hide;
})

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