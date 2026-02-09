<template>
  <v-tooltip top>
    <template v-slot:activator="{ on, attrs }">
      <component class="mr-2" :is="element" v-bind="attrs" v-on="on">
        <v-icon v-if="type==='radio'">mdi-radiobox-marked</v-icon>
        <v-icon v-if="type==='select'">mdi-list-box-outline</v-icon>
        <v-icon v-if="type==='checkbox'">mdi-checkbox-marked-outline</v-icon>
        <v-icon v-if="type==='file'">mdi-paperclip</v-icon>
        <v-icon v-if="type==='integer'">mdi-numeric</v-icon>
        <v-icon v-if="type==='float'">mdi-counter</v-icon>
        <v-icon v-if="type==='range'">mdi-sort-numeric-ascending</v-icon>
        <v-icon v-if="type==='text'">mdi-text-long</v-icon>
        <v-icon v-if="type==='link'">mdi-link-variant</v-icon>
        <v-icon v-if="type==='date'">mdi-calendar</v-icon>
        <v-icon v-if="type==='datetime'">mdi-calendar-clock</v-icon>
        <v-icon v-if="type==='date_range'">mdi-calendar-range</v-icon>
        <v-icon v-if="type==='security'">mdi-calendar-range</v-icon>
        <v-icon v-if="type==='living'">mdi-calendar-range</v-icon>
        <v-icon v-if="type==='orv'">mdi-calendar-range</v-icon>
      </component>
    </template>
    <span>{{ labels[type] }}</span>
  </v-tooltip>
</template>

<script>
export default {
  name: "FormQuestionIcon",
  props: ['type', 'element'],
  computed: {
    labels() {
      const l = {};
      this.formQuestionTypes.forEach(item => l[item.key] = item.label)
      return l;
    }
  }
}
</script>

<style scoped>

</style>