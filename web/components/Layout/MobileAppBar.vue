<template>
  <div class="mob-app-bar">
    <div class="container">
      <div class="flex align-items-center justify-content-between" style="padding-top: 5px;">
        <p class="text-subtitle mb-0 mt-0">
          <SvgIcon name="idea"/>
          В приложении удобней
        </p>
        <el-link target="_blank" class="el-button el-button--small ml-auto" type="info" :href="link">Скачать
          приложение
        </el-link>
        <el-button class="text-white btn-close" link @click="emit('close',true)">
          <SvgIcon name="close"/>
        </el-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, getMobileOperatingSystem, onMounted} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {useDeviceStore} from "~/stores/device";

const emit = defineEmits(['close']);
const type = ref<string>();
const {iosLink, androidLink} = useDeviceStore();
onMounted(() => {
  type.value = getMobileOperatingSystem();
});

const link = computed(() => {
  if (type.value === 'ios') {
    return iosLink
  }
  return androidLink;
})
</script>

<style scoped lang="scss">
.mob-app-bar {
  background: #FAB80F;
  height: 40px;
  position: sticky;
  top: 0;
  z-index: 33333;

  .text-subtitle {
    display: flex;
    align-items: center;
    column-gap: 5px;
  }

  .btn-close {
    width: 30px;
    height: 30px;
    padding: 0;
    margin: 0 0 0 2px;
  }

  .el-link--info {
    background: #0c5460;
    color: #ffffff;
    border: none;
    padding: 4px 10px;
  }
}
</style>