<template>
  <div>
    <v-menu offset-y v-if="notification.progress>0 && notification.progress < 100">
      <template v-slot:activator="{ on, attrs }">
        <v-btn
            icon
            color="primary"
            v-bind="attrs"
            v-on="on"
        >
          <v-icon>mdi-content-copy</v-icon>
        </v-btn>
      </template>
      <v-list>
        <v-list-item @click="copy()" link>
          <v-list-item-title >Копировать полностью</v-list-item-title>
        </v-list-item>
        <v-list-item @click="copy(true)" link>
          <v-list-item-title >Без отправленных</v-list-item-title>
        </v-list-item>

      </v-list>
    </v-menu>
    <v-btn v-else icon color="primary" @click="copy()">
      <v-icon>mdi-content-copy</v-icon>
    </v-btn>

    <v-btn v-if="notification.status==='draft'" icon color="success" @click="send()">
      <v-icon>mdi-play</v-icon>
    </v-btn>
    <v-btn v-else-if="notification.status==='scheduled'" icon color="danger" @click="cancel()">
      <v-icon>mdi-cancel</v-icon>
    </v-btn>
    <v-btn v-else-if="notification.status==='sending'" icon color="primary" @click="pause()">
      <v-icon>mdi-pause</v-icon>
    </v-btn>
    <v-btn v-else-if="notification.status==='paused'" icon color="primary" @click="resume()">
      <v-icon>mdi-play</v-icon>
    </v-btn>
  </div>
</template>

<script>
export default {
  name: "NotificationActions",
  props: ['notification'],
  methods: {
    async pause() {
      const {status} = await this.$store.dispatch("notificationPause", this.notification);
      this.$emit("input", status)
    },
    async resume() {
      const {status} = await this.$store.dispatch("notificationResume", this.notification);
      this.$emit("input", status)
    },
    async cancel() {
      const {status} = await this.$store.dispatch("notificationCancel", this.notification);
      this.$emit("input", status)
    },
    async send() {
      const {status} = await this.$store.dispatch("notificationSend", this.notification);
      this.$emit("input", status)
    },
    async copy(ignoreSent = false) {
      const notification = await this.$store.dispatch("notificationCopy", {notification: this.notification, ignoreSent: ignoreSent});
      this.$emit("created", notification);
    }
  }
}
</script>

<style scoped>

</style>