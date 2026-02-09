<template>
  <div class="layout">
    <div class="container green">
      <div class="header">
        <SvgIcon name="logo_white" class="m-logo"/>
        <el-button circle text link @click="emit('close',false)">
          <SvgIcon name="close" class="m-logo"/>
        </el-button>
      </div>
    </div>
    <div class="container">
      <div class="text-center img-block">
        <el-image :src="Image" alt="mobile app"/>
      </div>
    </div>
    <div class="container mt-40">
      <h2 class="text-h2 text-center">Приложение ASTT.SU</h2>
      <p class="text-gray text-center">
        Несколько сотен компаний с собственным парком техник. Ежедневные обновления.
        Акутальные заявки на спецтехнику.
      </p>
      <div class="actions">
        <GooglePlayButton v-if="type==='android'"/>
        <AppStoreButton v-if="type==='ios'"/>
        <el-link class="mt-10" type="primary" @click="emit('close',true)">Спасибо, не нужно</el-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import Image from '~/assets/images/mob_app_mob.png';
import GooglePlayButton from "~/components/Common/GooglePlayButton.vue";
import AppStoreButton from "~/components/Common/AppStoreButton.vue";
import {onMounted, getMobileOperatingSystem} from "#imports";

const emit = defineEmits(['close']);
const type = ref<string>();

onMounted(() => {
  type.value = getMobileOperatingSystem();
})
</script>

<style scoped lang="scss">
.layout {
  height: 100vh;
  width: 100vw;
  overflow-x: hidden;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 33333333;
  background: white;

  .container {
    &.green {
      background: #2C909B;
    }

    .header {
      height: 48px;
      display: flex;
      align-items: center;
      width: 100%;
      justify-content: space-between;
      //margin-bottom: 30px;

      .el-button {
        padding: 0;
        margin: 0;
        color: white;
        width: 30px;
        height: 30px;
      }
    }

    .img-block {
      margin-bottom: -20px;
      border-radius: 0 0 37% 37%;
      overflow: hidden;
      margin-left: -50px;
      margin-right: -50px;
      background: #2C909B;

      .el-image {
        margin-top: 35px;
        margin-bottom: -20px;
        width: 276px;
      }
    }

    .actions {
      text-align: center;
      margin-top: 60px;
      display: flex;
      flex-flow: column;
      row-gap: 10px;
      margin-bottom: 60px;
    }
  }
}
</style>