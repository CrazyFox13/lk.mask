<template>
  <el-input @keydown.enter="emit('submit')" :placeholder="placeholder" v-model="value">
    <template #suffix>
      <SvgIcon class="pointer" @click="emit('submit')" v-bind:class="{'text-black':black}" name="search"/>
    </template>
  </el-input>
</template>

<script setup lang="ts">

import SvgIcon from "~/components/SvgIcon.vue";
import {watch} from "#imports";

const props = defineProps(['placeholder', 'black', 'modelValue']);

const emit = defineEmits(['update:modelValue', 'submit']);

const value = ref(props.modelValue);

watch(value, () => {
  emit("update:modelValue", value.value);
});
</script>

<style scoped>
.pointer {
  cursor: pointer;
}
</style>