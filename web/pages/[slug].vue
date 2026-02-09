<template>
  <div class="page">
    <div class="container">
      <div class="custom-page-content" v-if="page" v-html="page.content"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, definePageMeta, useHead, useRoute, useRouter, useSeoMeta, useServerSeoMeta} from "#imports";

const route = useRoute();
const router = useRouter();
const slug = route.params.slug;
const page = ref();

try {
  const data = await apiFetch(`materials/${slug}`);
  page.value = data.page;
  useSeoMeta({
    title: page.value.seo_title,
    ogTitle: page.value.seo_title,
    description: page.value.seo_description,
    ogDescription: page.value.seo_description,
  })

  useHead({
    meta: [
      {name: 'keywords', content: page.value.seo_keywords}
    ],
  })

} catch (e) {
  throw createError({
    statusCode: 404,
    statusMessage: 'Page Not Found'
  })
  router.replace('/404');
}
</script>

<style lang="scss">
.custom-page-content {
  img {
    max-width: 100%;
  }
}
</style>