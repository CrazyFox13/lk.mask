<template>
  <div class="photo" v-bind:class="{'viewable':viewable}" @click="emit('open')">
    <el-image fit="cover" :src="photo"/>
    <el-button v-if="deletable" circle class="btn-delete" @click.prevent="emit('deleted')">
      <SvgIcon name="trash"/>
    </el-button>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";

defineProps(['photo', 'deletable', 'viewable']);
const emit = defineEmits(['deleted', 'open']);
</script>

<style scoped lang="scss">
.photo {
  position: relative;

  &.viewable {
    cursor: pointer;
  }

  .el-image {
    width: 95px;
    height: 95px;
  }

  @media (min-width: 992px) {
    .el-image {
      width: 132px;
      height: 132px;
    }
  }


  .btn-delete {
    background: rgba(34, 34, 34, 0.8);
    border-radius: 100px;
    width: 24px;
    height: 24px;
    padding: 0;
    position: absolute;
    bottom: 10px;
    right: 5px;
    color: #ffffff;
  }
}
</style>