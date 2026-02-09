<template>
  <v-list>
    <v-list-item v-for="(answer,idx) in answers" :key="answer.id">
      <v-list-item-subtitle>{{ answer.question.label }}</v-list-item-subtitle>
      <v-list-item-title>
        <div v-if="edit">
          <CheckboxForm
              v-model="answer.value"
              v-if="answer.question.type==='checkbox'"
          />
          <DateForm
              v-model="answer.value"
              v-if="answer.question.type==='date'"
          />
          <DateRangeForm
              v-model="answer.value"
              v-if="answer.question.type==='date_range'"
          />
          <DateTimeForm
              v-model="answer.value"
              v-if="answer.question.type==='datetime'"
          />
          <FileForm
              v-model="answer.value"
              v-if="answer.question.type==='file'"
          />
          <FloatForm
              v-model="answer.value"
              v-if="answer.question.type==='float'"
          />
          <IntegerForm
              v-model="answer.value"
              v-if="answer.question.type==='integer'"
          />
          <LinkForm
              v-model="answer.value"
              v-if="answer.question.type==='float'"
          />
          <LivingForm
              v-model="answer.value"
              v-if="answer.question.type==='living'"
          />
          <ORVForm
              v-model="answer.value"
              v-if="answer.question.type==='orv'"
          />
          <RadioForm
              v-model="answer.value"
              v-if="answer.question.type==='radio'"
              :options="answer.question.options"
          />
          <RangeForm
              v-model="answer.value"
              v-if="answer.question.type==='range'"
          />
          <SecurityForm
              v-model="answer.value"
              v-if="answer.question.type==='security'"
          />
          <SelectForm
              v-model="answer.value"
              v-if="answer.question.type==='select'"
              :options="answer.question.options"
          />
          <TextForm
              v-model="answer.value"
              v-if="answer.question.type==='text'"
          />
          <div class="text-caption error--text" v-if="errors[`form_answers.${idx}.value`]">
            Поле обязательно для заполнения
          </div>
        </div>
        <span v-else>{{ answer.value.replace("true","Да").replace("false", "Нет") }}</span>
      </v-list-item-title>
    </v-list-item>
  </v-list>
</template>

<script>

import CheckboxForm from "@/components/Order/QuestionTypes/CheckboxForm";
import DateForm from "@/components/Order/QuestionTypes/DateForm";
import DateRangeForm from "@/components/Order/QuestionTypes/DateRangeForm";
import DateTimeForm from "@/components/Order/QuestionTypes/DateTimeForm";
import FileForm from "@/components/Order/QuestionTypes/FileForm";
import FloatForm from "@/components/Order/QuestionTypes/FloatForm";
import IntegerForm from "@/components/Order/QuestionTypes/IntegerForm";
import LinkForm from "@/components/Order/QuestionTypes/LinkForm";
import LivingForm from "@/components/Order/QuestionTypes/LivingForm";
import ORVForm from "@/components/Order/QuestionTypes/ORVForm";
import RadioForm from "@/components/Order/QuestionTypes/RadioForm";
import RangeForm from "@/components/Order/QuestionTypes/RangeForm";
import SecurityForm from "@/components/Order/QuestionTypes/SecurityForm";
import SelectForm from "@/components/Order/QuestionTypes/SelectForm";
import TextForm from "@/components/Order/QuestionTypes/TextForm";

export default {
  name: "FormAnswers",
  components: {
    TextForm,
    SelectForm,
    SecurityForm,
    RangeForm,
    RadioForm,
    ORVForm,
    LivingForm,
    LinkForm, IntegerForm, FloatForm, FileForm, DateTimeForm, DateRangeForm, DateForm, CheckboxForm
  },
  props: ['value', 'edit', 'errors'],
  data() {
    return {
      answers: this.value,
    }
  },
  watch: {
    answers: {
      handler() {
        this.$emit("input", this.answers);
      }, deep: true
    }
  }
}
</script>

<style scoped>

</style>