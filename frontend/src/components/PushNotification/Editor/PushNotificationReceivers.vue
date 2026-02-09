<template>
  <v-row class="mt-2">
    <v-col cols="6">
      <ReceiversFilter v-model="query" :pushNotification="pushNotification"/>
      <v-btn class="mt-4" small color="secondary" @click="attachAll()">Выбрать всех</v-btn>
      <AvailableReceivers
          class="mt-4"
          :push-notification="pushNotification"
          :query="query"
          :re-fetch="reFetch"
      />
    </v-col>
    <v-col cols="6">
      <v-btn small class="mt-11" color="secondary" @click="detachAll()">Убрать всех</v-btn>
      <AttachedReceivers
          class="mt-4"
          :push-notification="pushNotification"
          :re-fetch="reFetch"
      />
    </v-col>
  </v-row>
</template>

<script>
import ReceiversFilter from "@/components/PushNotification/Editor/ReceiversFilter";
import AvailableReceivers from "@/components/PushNotification/Editor/AvailableReceivers";
import AttachedReceivers from "@/components/PushNotification/Editor/AttachedReceivers";

export default {
  name: "PushNotificationReceivers",
  components: {AttachedReceivers, AvailableReceivers, ReceiversFilter},
  props: ['pushNotification'],
  data() {
    return {
      query: {},
      reFetch: false,
    }
  },
  methods: {
    attachAll() {
      this.$http.post(`push-notifications/${this.pushNotification.id}/attach-all`, {
        filter: this.query,
      }).then(() => {
        this.reFetchUsers();
        this.$emit('users_updated');
      })
    },
    detachAll() {
      this.$http.post(`push-notifications/${this.pushNotification.id}/detach-all`).then(() => {
        this.reFetchUsers();
        this.$emit('users_updated')
      })
    },
    reFetchUsers() {
      this.reFetch = true;
      this.$nextTick(() => {
        this.reFetch = false;
      });
    },
  }
}
</script>

<style scoped>

</style>