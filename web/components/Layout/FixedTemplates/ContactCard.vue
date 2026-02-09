<template>
  <div class="hidden-md-and-up bg-white">
    <div class="container text-center">
      <div class="buttons mt-20">
        <el-button
            v-if="order_id"
            :type="offer_id?'info':'primary'"
            :disabled="offer_id"
            class="py-2"
            size="small"
            @click="onOfferCreate()"
        >
          <SvgIcon name="message-dollar" class="mr-2"/>
          Откликнуться
        </el-button>
        <el-link
            v-if="link"
            :data-href="hidden?'':link"
            v-bind:class="{'el-button--secondary':hidden,'el-button--info':!hidden}"
            @click.prevent="onClick"
            class="el-button el-button--small w-100"
        >
          <SvgIcon v-if="hidden" name="phone" class="mr-2"/>
          {{ hidden ? hiddenValue : shownValue }}
        </el-link>
      </div>

      <p class="mt-10 " v-bind:class="{'blur':!user}">
        {{ title }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {useAuthStore} from "~/stores/user";
import {emitter, ref, useRouter} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";

const props = defineProps(['title', 'shownValue', 'hiddenValue', 'link', 'order_id', 'offer_id']);
const hidden = ref(true);

const {user} = storeToRefs(useAuthStore());

const router = useRouter();

const onClick = (e: any) => {
  if (!user.value) {
    try {
      (window as any).ym(65983300, 'reachGoal', 'show_phone_guest')
    } catch (e) {
      console.log("ym", 'show_phone_guest', e)
    }
    router.push('/auth/sign-up');
    return;
  }
  if (!user.value.allowed_to_show_contacts) {
    router.push('/profile');
    return;
  }
  if (hidden.value) {
    hidden.value = false;
    try {
      (window as any).ym(65983300, 'reachGoal', 'show_phone')
    } catch (e) {
      console.log('ym', 'show_phone', e);
    }
    return;
  }
  try {
    (window as any).ym(65983300, 'reachGoal', 'click_phone')
  } catch (e) {
    console.log("ym", 'click_phone', e)
  }
  window.location.href = e.target.closest('a').dataset.href;
}

const onOfferCreate = () => {
  if (!user.value) {
    router.push('/auth/sign-up');
    return
  }
  if (!user.value.allowed_to_show_contacts) {
    router.push('/profile');
    return;
  }
  emitter.emit("offer-modal")
}
</script>

<style scoped>
.bg-white {
  padding: 5px 0;
}

.blur {
  filter: blur(3px);
}

.buttons {
  display: flex;
  column-gap: 12px;

  .el-button {
    width: 100%;
    padding: 8px 10px;
    margin: 0;
  }
}
</style>