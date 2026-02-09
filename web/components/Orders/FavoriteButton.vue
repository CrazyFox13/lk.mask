<template>
  <el-button link @click.prevent="toggleFavorite" v-bind:class="{'no-border':filled,'visible':filled}">
    <SvgIcon v-if="filled" name="favorite_filled"/>
    <SvgIcon v-else name="favorite"/>
  </el-button>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, useRouter} from "#imports";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

const router = useRouter();
const {user} = storeToRefs(useAuthStore());

const props = defineProps(['order']);
const filled = ref<boolean>(props.order.is_favorite);

const toggleFavorite = async () => {
  console.log("toggleFavorite");
  if (!user.value) {
    await router.push('/auth/sign-up');
    return;
  }

  await apiFetch(`orders/${props.order.id}/favorite`, {
    method: 'post'
  }, false);
  filled.value = !filled.value;
}
</script>

<style scoped>
.no-border {
  border: none !important;
}

.visible {
  visibility: visible;
}
</style>