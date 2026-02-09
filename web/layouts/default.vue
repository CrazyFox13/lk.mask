<template>
  <TopBar class="top-bar"/>
  <MobileAppBar v-if="showMobBar" @close="onMobBarClosed"/>
  <HeaderSection class="header-section"/>
  <MobileHeader class="mobile-header"/>
  <el-config-provider :locale="ru">
    <NuxtPage/>
  </el-config-provider>
  <FooterSection class="footer-section"/>
  <MobileMenu class="hidden-md-and-up"/>
  <DelayHydration>
    <client-only>
      <GeoCityDialog v-model="cityDialog"/>
    </client-only>
  </DelayHydration>
  <DelayHydration>
    <FixedContent>
      <template #content>
        <ContactCard
            v-if="phoneCard.title"
            :hidden-value="phoneCard.hiddenValue"
            :shown-value="phoneCard.shownValue"
            :title="phoneCard.title"
            :link="phoneCard.link"
            :order_id="phoneCard.order_id"
            :offer_id="phoneCard.offer_id"
        />
      </template>
    </FixedContent>
  </DelayHydration>
  <DelayHydration>
    <ReportChat v-if="showChat" @close="showChat = false" :chat_id="chatData.id" :report_id="chatData.report_id"/>
  </DelayHydration>
  <DelayHydration>
    <MobileOverflow v-if="showOverFlow" @close="onOverflowClose"/>
  </DelayHydration>
</template>

<script setup>
import TopBar from "../components/Layout/TopBar";
import HeaderSection from "../components/Layout/HeaderSection";
import FooterSection from "../components/Layout/FooterSection";
import MobileHeader from "../components/Layout/MobileHeader";
import 'element-plus/theme-chalk/display.css'
import MobileMenu from "../components/Layout/MobileMenu";
import {useAuthStore} from "../stores/user";
import {onMounted} from "#imports";
import {emitter} from "../composables/emitter";
import GeoCityDialog from "../components/Layout/GeoCityDialog";
import {ElConfigProvider} from 'element-plus'

import ru from 'element-plus/dist/locale/ru.mjs'
import FixedContent from "../components/Layout/FixedContent";
import ContactCard from "../components/Layout/FixedTemplates/ContactCard";
import ReportChat from "../components/Profile/Reports/ReportChat";
import MobileOverflow from "../components/Layout/MobileOverflow";
import {useDeviceStore} from "../stores/device";
import {storeToRefs} from "pinia";
import {useCookie} from "#app";
import MobileAppBar from "../components/Layout/MobileAppBar";

const authStore = useAuthStore()
const {getGeoData, getUser} = authStore;

const showOverFlow = ref(false);

const {isMobile} = storeToRefs(useDeviceStore())
const overflowCookie = useCookie('mob-overflow');

const route = useRoute()
useHead(() => ({
  link: [
    {
      rel: 'canonical',
      href: 'https://astt.su' + route.path,
    },
  ],
}))

const onOverflowClose = (remember) => {
  showOverFlow.value = false;
  if (remember) {
    overflowCookie.value = '1';
  } else {
    const tempCookie = useCookie('mob-overflow', {maxAge: 60 * 60 * 24});
    tempCookie.value = '1';
  }
}

const showMobBar = ref(false);
const mobBarCookie = useCookie('mob-top-bar');
const onMobBarClosed = (remember) => {
  showMobBar.value = false;
  if (remember) {
    mobBarCookie.value = '1';
  }
}

onMounted(() => {
  getGeoData();
  if (isMobile.value && !overflowCookie.value) {
    showOverFlow.value = true;
  }

  if (isMobile.value && !mobBarCookie.value) {
    showMobBar.value = true;
  }
})

const cityDialog = ref(false);
emitter.on('cityDialog', () => {
  cityDialog.value = true;
});

const showChat = ref(false);
const chatData = ref();
emitter.on('chat', (data) => {
  if (showChat.value) {
    showChat.value = false;
  }
  setTimeout(() => {
    chatData.value = data
    showChat.value = true;
  })
});

const phoneCard = ref({
  title: "",
  hiddenValue: "",
  shownValue: "",
  link: "",
  order_id: 0,
  offer_id: 0,
});
emitter.on("fixed-contact", (data) => {
  phoneCard.value = data
})

</script>

<style scoped></style>