<template>
  <div v-bind:class="{'input-error':!!error}">
    <label class="input-title" v-if="!NOT_LABELED_QUESTIONS.includes(question.type)">
      {{ question.label }}
    </label>
    <div>
      <el-select
          v-if="question.type==='select'"
          placeholder="Не выбрано"
          v-model="value.value"
      >
        <el-option
            v-for="(option,i) in question.options"
            :key="i"
            :label="option"
            :value="option"
        />
      </el-select>
      <el-switch
          v-if="question.type==='checkbox' || question.type==='orv'"
          :active-text="question.label"
          v-model="value.value"
          :inactive-value="'false'"
          :active-value="'true'"
      />
      <el-input
          v-if="question.type==='text'"
          placeholder="Введите значение"
          v-model="value.value"
      />
      <el-input-number
          v-if="question.type==='integer'"
          v-model="value.value"
      />
      <p class="text-sub-subtitle text-error" v-if="!!error">Поле обязательно для заполнения</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, watch} from "#imports";
import type {IFormAnswer} from "~/types/formAnswer";

const NOT_LABELED_QUESTIONS = ['checkbox','orv'];
const props = defineProps(['question', 'errors', 'index', 'modelValue']);
const emit = defineEmits(['update:modelValue']);
const value = ref<IFormAnswer>(props.modelValue ? props.modelValue : {
  form_question_id: props.question.id,
  value: undefined
});
const error = computed(() => {
  return props.errors[`form_answers.${props.index}.value`];
});

watch(value.value, () => {
  emit('update:modelValue', value.value);
}, {deep: true});
</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
}
</style>