<template>
  <div>
    <div class="token-container">
      <div class="trigger" @click.prevent="open()">
        <SvgIcon name="adv_more"/>
      </div>
      <div class="body" v-bind:class="{'bottom':isVertical,'left':!isVertical}" v-if="show">
        <div class="body-top">
          <SvgIcon name="close" @click.prevent="close()"/>
        </div>
        <div class="text-subtitle mb-10">О рекламодателе</div>
        <div class="text-subtitle content">{{ banner.advertiser.name }}</div>
        <div class="text-subtitle content">ИНН {{ banner.advertiser.inn }}</div>
        <div class="text-subtitle content">ERID {{ banner.token }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {computed, ref} from "#imports";

const props = defineProps({
  banner: {
    type: Object as any,
  }
})

const show = ref(false)

const open = () => {
  show.value = true;
}

const close = () => {
  show.value = false;
}

const isVertical = props.banner.place.width < props.banner.place.height;
</script>

<style scoped lang="scss">
.token-container {
  position: relative;

  .trigger {
    cursor: pointer;
  }

  .body {
    background: white;
    padding: 4px 12px 12px 12px;
    border-radius: 8px;
    position: absolute;

    &-top {
      display: flex;
      justify-content: flex-end;
    }

    &.left {
      top: -3px;
      right: 40px;
      width: 256px;
    }
    &.bottom {
      top: 32px;
      right: 0;
      width: 210px;
    }


    .content {
      color: #72767B;
    }
  }
}
</style>