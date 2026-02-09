<template>
  <div>
    <FAQItem v-for="page in pages" :key="page.id" :page="page"/>
  </div>
</template>

<script setup lang="ts">
import {apiFetch} from "#imports";
import FAQItem from "~/components/Help/FAQItem.vue";

const props = defineProps(['currentTab']);
const path = props.currentTab.path.replace("/faq", '')
const {pages} = await apiFetch(`pages?active=1&type=faq&path=${encodeURIComponent(path)}`)
</script>

<style scoped lang="scss">

</style>