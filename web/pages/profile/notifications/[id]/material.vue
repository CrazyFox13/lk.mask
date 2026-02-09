<template>
  <div>
    <Preloader v-if="loading"/>
    <el-card v-else>
      <div class="flex align-items-center mb-25">
        <nuxt-link class="el-button el-button--info link-circle mr-5" to="/profile/notifications">
          <SvgIcon name="back"/>
        </nuxt-link>
        <div class="text-h3">Уведомления</div>
      </div>
      <div class="notification-material" v-html="material"/>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, nextTick, ref, useAsyncData, useRoute} from "#imports";
import Preloader from "~/components/Preloader.vue";
import SvgIcon from "~/components/SvgIcon.vue";

const route = useRoute();

const material = ref();
const loading = ref(false);

const getNotification = async () => {
  loading.value = true;
  const data = await apiFetch(`push-notifications/${route.params.id}/material`, {}, true);
  material.value = data.material;
  await nextTick(() => {
    loading.value = false;
  })
}

useAsyncData(() => getNotification());

</script>

<style>
.notification-material {
  img {
    max-width: 100%;
  }
}
</style>