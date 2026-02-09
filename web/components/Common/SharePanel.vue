<template>
  <div style="position: relative">
    <div @click.prevent="open">
      <slot name="default"/>
    </div>
    <Teleport to="body">
      <el-card
          v-click-away="onClose"
          v-if="share"
          v-bind:style="{'left':`${cardPosition.x}px`,'top':`${cardPosition.y}px`}"
      >
        <div class="icons">
          <el-button link @click.prevent="tgShare()">
            <el-image :src="tgImage"/>
          </el-button>
          <el-button link @click.prevent="waShare()">
            <el-image :src="waImage"/>
          </el-button>
          <el-button link @click.prevent="okShare()">
            <el-image :src="okImage"/>
          </el-button>
          <el-button link @click.prevent="vkShare()">
            <el-image :src="vkImage"/>
          </el-button>
        </div>
        <el-input readonly :model-value="link"/>
        <el-link role="button" type="primary" @click.prevent="copy()">Скопировать ссылку</el-link>
      </el-card>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import tgImage from '@/assets/images/share_tg.png';
import vkImage from '@/assets/images/share_vk.png';
import okImage from '@/assets/images/share_ok.png';
import waImage from '@/assets/images/share_wa.png';
import {nextTick} from "#imports";

const props = defineProps(['link', 'text']);
const emit = defineEmits(['close']);
const share = ref(false);
const containerSize = {
  width: 180,
  height: 180
};

const cardPosition = ref({
  x: 0,
  y: 0
});

const offset = 10;

const open = (e: any) => {
  const {pageX, pageY} = e;
  const {clientHeight, clientWidth} = document.body;
  if (pageX + containerSize.width < clientWidth) {
    // right
    cardPosition.value.x = pageX + offset;
  } else {
    // left
    cardPosition.value.x = pageX - containerSize.width - offset;
  }

  if (pageY + containerSize.height < clientHeight) {
    // bottom
    cardPosition.value.y = pageY + offset;
  } else {
    // top;
    cardPosition.value.y = pageY - containerSize.height - offset;
  }
  share.value = true;
}

const onClose = () => {
  nextTick(() => {
    if (share.value) {
      share.value = false;
    }
  })
}

const copy = () => {
  share.value = false;
}

const tgShare = () => {
  const value = props.text ? props.text : props.link;
  window.open(
      `https://telegram.me/share/?url=${encodeURIComponent(value)}`,
      '__blank'
  );
}

const waShare = () => {
  const value = props.text ? props.text : props.link;
  //const text = [encodeURIComponent(props.link), props.text ? encodeURIComponent(props.text) : ''].filter(i => i).join(': ')
  window.open(
      `https://api.whatsapp.com/send?text=${encodeURIComponent(value)}`,
      '__blank'
  );
}

const okShare = () => {
  const url = `https://connect.ok.ru/offer?url=${encodeURIComponent(props.link)}`;
  window.open(url, "_blank")
}

const vkShare = () => {
  const url = `https://vk.com/share.php?url=${encodeURIComponent(props.link)}&noparse=true&image=${encodeURIComponent('https://storage.yandexcloud.net/astt-su/0_service/astt_logo.png')}`;
  window.open(url, "_blank")
}
</script>

<style scoped lang="scss">
.el-card {
  padding: 16px;
  max-width: 144px;
  position: absolute;
  z-index: 333;
  top: 24px;
  left: 17px;
  visibility: visible;

  .icons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  /*.el-input {
    width: 144px;
  }*/
}
</style>