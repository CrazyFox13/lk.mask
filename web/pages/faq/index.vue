<template>
  <div>
    <FAQItem v-for="page in pages" :key="page.id" :page="page"/>
  </div>
</template>

<script setup lang="ts">
import {apiFetch} from "#imports";
import FAQItem from "~/components/Help/FAQItem.vue";

const {pages} = await apiFetch(`pages?active=1&type=faq&path=${encodeURIComponent('/')}`)
</script>

<style scoped lang="scss">

</style>