<template>
  <div class="score-item">
    <div class="score-value" v-bind:class="{'positive':detail.positive,'negative':!detail.positive}">
      {{ detail.score }}
    </div>
    <div class="score-content">
      <div class="text-h5">{{ detail.title }}</div>
      <p class="text-subtitle text-gray" v-if="showDescription" v-html="detail.description"></p>
      <el-link type="primary" class="text-subtitle" @click="showDescription = !showDescription">
        {{ showDescription ? 'Свернуть' : 'Подробнее' }}
      </el-link>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps(['detail']);

const showDescription = ref(false);
</script>

<style scoped lang="scss">
.score-item {
  display: flex;
  column-gap: 10px;
  flex-wrap: wrap;

  .score-value {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 16px;
    line-height: 20px;
    font-family: 'Roboto Bold', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    margin-bottom: 10px;

    &.positive {
      background-color: #1C7F98;
    }

    &.negative {
      background-color: #F92609;
    }
  }

  .score-content {
    width: 100%;

    @media (min-width: 992px) {
      width: auto;
    }

    .text-gray {
      max-width: 700px;
      margin: 0;
    }
  }
}
</style>