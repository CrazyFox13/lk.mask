<template>
  <v-card>
    <v-card-title>Редактор вопроса</v-card-title>
    <v-card-subtitle></v-card-subtitle>
    <v-card-text>
      <v-select
          label="Тип"
          v-model="question.type"
          :items="formQuestionTypes"
          item-value="key"
          item-text="label"
          :error-messages="errors.type"
          :error-count="1"
          :error="!!errors.type"
      />
      <v-switch
          v-model="question.required"
          label="Обязателен для заполнения"
      />
      <v-text-field
          v-if="needLabel"
          label="Текст вопроса"
          v-model="question.label"
          :error-messages="errors.label"
          :error-count="1"
          :error="!!errors.label"
      />
      <v-autocomplete
          v-if="needOptions"
          v-model="question.options"
          label="Варианты на выбор"
          :items="options"
          chips
          deletable-chips
          multiple
          @keydown.enter.stop="addOption"

          :error-messages="errors.options"
          :error-count="1"
          :error="!!errors.options"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "FormQuestionEditor",
  props: ['value', 'group_id', 'type_id'],
  data() {
    return {
      question: this.value,
      errors: {},
      options: []
    }
  },
  computed: {
    needOptions() {
      return this.formQuestionTypes.find(i => i.key === this.question.type)?.options;
    },
    needLabel() {
      return !this.formQuestionTypes.find(i => i.key === this.question.type)?.default_label;
    },

  },
  created() {
    this.options = this.question.options ? this.question.options : [];
  },
  methods: {
    save() {
      this.errors = {};
      if (this.question.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`vehicle-groups/${this.group_id}/vehicle-types/${this.type_id}/form-questions`, this.question).then(r => {
        this.$emit('created', r.body.formQuestion);
        this.$emit('close');
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`vehicle-groups/${this.group_id}/vehicle-types/${this.type_id}/form-questions/${this.question.id}`, this.question).then(r => {
        this.$emit('input', r.body.formQuestion);
        this.$emit('close');
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    addOption(e) {
      if (!Array.isArray(this.question.options)) {
        this.question.options = [];
      }
      const option = e.target.value;

      if (this.options.indexOf(option) === -1) this.options.push(option)
      if (this.question.options.indexOf(option) === -1) this.question.options.push(option)

      e.target.value = '';
    }
  }
}
</script>

<style scoped>

</style>