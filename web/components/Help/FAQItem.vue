<template>
  <el-card class="mb-15 faq">
    <div class="flex justify-between align-items-center">
      <div class="text-h4">{{ page.title }}</div>

      <el-button v-bind:class="{'active':showContent}" circle @click="showContent = !showContent">
        <SvgIcon name="chevron-down"/>
      </el-button>
    </div>
    <div v-html="page.content" v-if="showContent"/>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";

defineProps(['page']);

const showContent = ref(false);

</script>

<style lang="scss">
.faq {
  padding: 14px !important;

  @media (min-width: 992px) {
    padding: 20px;
  }

  .el-button {
    color: #222222;
    background: #E8EBF1;
    padding: 0;
    width: 36px;
    height: 36px;

    &.active {
      color: white;
      background: #222222;

      svg {
        transform: rotate(-180deg);
      }
    }
  }
}
</style>