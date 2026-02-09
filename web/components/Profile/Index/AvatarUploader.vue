<template>
  <div class="ava-button">
    <el-image :src="avatar"/>
    <el-button type="info" circle>
      <SvgIcon name="camera"/>
    </el-button>
  </div>
</template>

<script setup lang="ts">
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {computed} from "#imports";

const {user} = storeToRefs(useAuthStore());

const avatar = computed(() => {
  if (user.value && user.value.avatar) return user.value.avatar;
  return DefaultAvatar
});
</script>

<style scoped lang="scss">
.ava-button {
  width: 80px;
  height: 80px;
  position: relative;
  cursor: pointer;

  .el-button {
    position: absolute;
    right: 0;
    bottom: 0;
    background-color: #E8EBF1;
  }

  .el-image {
    border-radius: 50%;
  }
}
</style>