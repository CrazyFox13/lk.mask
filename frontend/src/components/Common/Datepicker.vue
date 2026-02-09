<template>
  <v-menu
      v-model="menu"
      :close-on-content-click="false"
      :nudge-right="40"
      transition="scale-transition"
      offset-y
      min-width="auto"
  >
    <template v-slot:activator="{ on, attrs }">
      <v-text-field
          :value="readValue"
          :label="label"
          prepend-icon=""
          readonly
          v-bind="attrs"
          v-on="on"
          :error-messages="error"
          :error-count="1"
          :error="!!error"
      ></v-text-field>
    </template>
    <v-date-picker
        v-model="input"
        @input="range?'':menu = false"
        :range="range"
        :first-day-of-week="1"
        :locale="'ru'"
    >
      <div v-if="range">
        <v-spacer></v-spacer>
        <v-btn
            text
            color="primary"
            @click="menu = false"
        >
          Отменить
        </v-btn>
        <v-btn
            text
            color="primary"
            @click="$emit('selected');menu = false"
        >
          Выбрать
        </v-btn>
      </div>
    </v-date-picker>
  </v-menu>
</template>

<script>
import moment from "moment";

export default {
  name: "DatePicker",
  props: ['value', 'label', 'range', 'error'],
  data() {
    return {
      menu: false,
      input: this.value,
    }
  },
  watch: {
    input() {
      this.$emit('input', this.input)
    }
  },
  computed: {
    readValue() {
      if (!this.input) return undefined;
      return moment(this.input).format("DD.MM.YYYY")
    }
  }
}
</script>

<style scoped>

</style>