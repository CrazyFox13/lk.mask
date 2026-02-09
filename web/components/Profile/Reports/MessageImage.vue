<template>
  <a :href="url" target="_blank" class="msg-image" v-bind:class="{'loading':loading}">
    <el-skeleton
        v-if="loading"
        animated
    >
      <template #template>
        <el-skeleton-item variant="image" style="width: 240px; height: 240px"/>
      </template>
    </el-skeleton>
    <el-image
        :src="url"
        fit="contain"
        @load="loading=false"
    />
  </a>
</template>

<script setup lang="ts">
defineProps(['url']);

const loading = ref(true);
</script>

<style lang="scss">
.msg-image {
  position: relative;

  .el-skeleton {
    //position: absolute;
    width: 240px
  }

  .el-image img {
    max-height: 150px;
    width: auto;
  }

  &.loading{

    .el-image{
      opacity: 0;
      position: absolute;
      width: 1px;
    }
  }
}
</style>