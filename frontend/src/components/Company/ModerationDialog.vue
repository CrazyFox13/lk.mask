<template>
  <v-card>
    <v-card-title>Модерация</v-card-title>
    <v-card-text>
      <v-select
          label="Модерация"
          v-model="moderationData.status"
          item-value="key"
          item-text="value"
          :items="companyModerationStatuses"
      />
      <v-textarea v-if="moderationData.status==='canceled'" label="Сообщение модерации"
                  v-model="moderationData.text"/>
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="moderate()">Изменить статус</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "ModerationDialog",
  props: ['value'],
  data() {
    return {
      moderationData: {},
      company: this.value
    }
  },
  created() {
    this.moderationData = {
      status: this.company.moderation_status,
      text: this.company.moderation_message,
    }
  },
  methods: {
    moderate() {
      let method;
      switch (this.moderationData.status) {
        case "draft":
          method = 'draft';
          break;
        case "moderation":
          method = 'moderate';
          break;
        case "approved":
          method = 'approve';
          break;
        case "canceled":
          method = 'cancel';
          break;
        default:
          return;
      }
      this.$emit('errors', {});
      this.$http.post(`companies/${this.company.id}/${method}`, {
        text: this.moderationData.text
      }).then(() => {
        this.company.moderation_status = this.moderationData.status;
        this.company.moderation_message = this.moderationData.text;
        this.$emit('input', this.company);
        this.$emit('close')
      }).catch(r => {
        this.$emit('errors', r.body.errors);
      })
    },
  }
}
</script>

<style scoped>

</style>