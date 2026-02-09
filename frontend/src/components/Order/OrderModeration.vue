<template>
  <v-card>
    <v-card-title>Статус заявки</v-card-title>
    <v-card-text>
      <v-select
          label="Выберите статус"
          v-model="moderationData.status"
          item-value="key"
          item-text="value"
          :items="orderModerationStatuses"
      />
      <v-textarea
          v-if="moderationData.status==='canceled'"
          label="Сообщение модерации"
          v-model="moderationData.text"/>
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="moderate()">Изменить статус</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "OrderModeration",
  props: ['value'],
  data() {
    return {
      order: this.value,
      moderationData: {}
    }
  },
  created() {
    this.moderationData = {
      status: this.order.moderation_status,
      text: this.order.moderation_message,
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
        case "on_approval":
          method = 'on-approval';
          break;
        case "approved":
          method = 'approve';
          break;
        case "in_progress":
          method = 'in-progress';
          break;
        case "canceled":
          method = 'cancel';
          break;
        case "removed":
          method = 'remove';
          break;
        case "completed":
          method = 'complete';
          break;

        default:
          return;
      }
      this.$http.post(`orders/${this.order.id}/${method}`, {
        text: this.moderationData.text
      }).then(() => {
        this.order.moderation_status = this.moderationData.status;
        this.order.moderation_message = this.moderationData.text;
        this.$emit('input', this.order);
        this.$emit('close');
      })
    },
  }
}
</script>

<style scoped>

</style>