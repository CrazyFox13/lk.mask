<template>
  <v-card>
    <v-card-title>Статус заявок ({{ ids.length }})</v-card-title>
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
      <v-btn color="primary" @click="submit()">Изменить статус</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "MultipleOrderModeration",
  props: ['ids'],
  data() {
    return {
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
    submit() {
      this.$http.post(`orders/multiple-moderation`, {
        ids: this.ids,
        status: this.moderationData.status,
        text: this.moderationData.text
      }).then(() => {
        this.$emit('changed');
        this.$emit('close');
      })
    },
  }
}
</script>

<style scoped>

</style>