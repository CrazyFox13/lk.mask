<template>
  <v-card>
    <v-card-title>Статус претензии</v-card-title>
    <v-card-text>
      <v-select
          label="Выберите статус"
          v-model="moderationData.status"
          item-value="key"
          item-text="value"
          :items="reportModerationStatuses"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="moderate()">Изменить статус</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "ReportModeration",
  props: ['value'],
  data() {
    return {
      report: this.value,
      moderationData: {}
    }
  },
  created() {
    this.moderationData = {
      status: this.report.status,
    }
  },
  methods: {
    moderate() {
      let method;
      switch (this.moderationData.status) {
        case "draft":
          method = 'draft';
          break;
        case "active":
          method = 'moderate';
          break;
        case "resolved":
          method = 'resolve';
          break;
        case "referee":
          method = 'referee';
          break;
        case "canceled":
          method = 'cancel';
          break;
        case "confirmed":
          method = 'confirm';
          break;
        case "rejected":
          method = 'reject';
          break;
        default:
          return;
      }
      this.$http.post(`reports/${this.report.id}/${method}`, {
        text: this.moderationData.text
      }).then(() => {
        this.report.status = this.moderationData.status;
        this.$emit('input', this.report);
        this.$emit('close');
      })
    },
  }
}
</script>

<style scoped>

</style>