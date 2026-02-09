<template>
  <div class="overlay">
    <div class="dialog">
      <el-button text class="close-btn" @click="emit('close')">
        <SvgIcon name="close"></SvgIcon>
      </el-button>
      <div class="content">
        <slot name="content"/>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";

const emit = defineEmits(['close']);
</script>

<style scoped lang="scss">
.overlay {
  position: fixed;
  z-index: 100;
  background: rgba(0, 0, 0, 0.55);
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;

  .dialog {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;

    .close-btn {
      color: #FFFFFF;
      position: absolute;
      width: 40px;
      height: 40px;
      padding: 0;
      right: 37px;
      top: 37px;

      @media (min-width: 99px) {
        right: 40px;
        top: 45px;
      }

      &:hover {
        color: #FFFFFF;;
        background: inherit;
      }
    }

    .content {
      max-width: 680px;
      width: calc(100% - 32px);
    }
  }
}
</style>