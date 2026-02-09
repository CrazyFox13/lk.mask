<template>
  <div class="all-card">
    <div class="item-name">Показать все категории</div>
    <img class="item-image" :src="Img"/>
  </div>
</template>

<script setup lang="ts">
import Img from "@/assets/images/all_categories_card_image.png"
</script>

<style scoped lang="scss">
.all-card {
  border-radius: 10px;
  overflow: hidden;
  position: relative;
  cursor: pointer;
  width: 115px;
  min-width: 115px;
  height: 90px;
  background-color: #F3F5F9;

  .item-name {
    font-weight: 500;
    font-size: 13px;
    line-height: 15px;
    font-family: "Roboto Medium", sans-serif;
    color: #222222;
    margin: 8px 8px;
    word-break: break-word;
  }

  .item-image {
    position: absolute;
    bottom: 0;
    right: 0;
    height: 62px;
    width: auto;
  }
}
</style>