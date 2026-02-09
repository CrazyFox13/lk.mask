<template>
  <div class="flex rating-counter" v-bind:style="{'column-gap':`${colGap}px`}">
    <StarFull :size="s" v-for="i in filledCount" :key="i"/>
    <StarHalf :size="s" v-if="halfExists"/>
    <StarEmpty :size="s" v-for="k in emptyCount" :key="k"/>
  </div>
</template>

<script setup lang="ts">
import StarFull from "~/components/Common/Icons/StarFull.vue";
import StarEmpty from "~/components/Common/Icons/StarEmpty.vue";
import StarHalf from "~/components/Common/Icons/StarHalf.vue";

const props = defineProps(['rating', 'size', 'colGap']);
const MAX_VALUE = 5;
const filledCount = Math.floor(Number(props.rating));
const halfExists = Number(props.rating) - filledCount >= .5;
const emptyCount = MAX_VALUE - filledCount - (halfExists ? 1 : 0);

const s = ref<number>(props.size ? Number(props.size) : 10);
</script>

<style lang="scss">
.rating-counter {
  span {
    display: inline-flex;
    margin-right: 1px;
  }
}
</style>