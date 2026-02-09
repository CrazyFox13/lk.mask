<template>
  <div>
    Найдено: {{ totalCount }}
    <v-list>
      <v-list-item link v-for="user in users" :key="user.id">
        <v-list-item-action @click="detach(user)">
          <v-btn icon>
            <v-icon>mdi-chevron-left</v-icon>
          </v-btn>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>{{ user.name }} {{ user.surname }}</v-list-item-title>
          <v-list-item-subtitle>{{ user.phone }}</v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
    </v-list>
    <v-pagination v-model="page" :length="pagesCount"/>
  </div>
</template>

<script>
export default {
  name: "AttachedReceivers",
  props: ['pushNotification', 'reFetch'],
  data() {
    return {
      users: [],
      page: 1,
      pagesCount: 1,
      totalCount: 0
    }
  },
  created() {
    this.getUsers();

    this.unsubscribe = this.$store.subscribe((mutation, state) => {
      if (mutation.type === 'attachUserToNotification') {
        if (state.attachedUser) {
          this.users.unshift(state.attachedUser);
          this.$store.commit('attachUserToNotification', undefined);
          this.totalCount++;
        }
      }
    });
  },
  beforeDestroy() {
    this.unsubscribe;
  },
  watch: {
    page: {
      handler() {
        this.getUsers();
      },
    },
    reFetch: {
      handler(v) {
        v ? this.getUsers() : undefined;
      }
    }
  },
  methods: {
    getUsers() {
      this.$http.get(`push-notifications/${this.pushNotification.id}/attached-receivers?page=${this.page}`).then(r => {
        this.users = r.body.users;
        this.totalCount = r.body.totalCount;
        this.pagesCount = r.body.pagesCount;
      })
    },
    detach(user) {
      this.$http.post(`push-notifications/${this.pushNotification.id}/detach/${user.id}`).then(() => {
        this.users.splice(this.users.indexOf(user), 1);
        this.$store.commit('detachUserFromNotification', user);
        this.totalCount--;
      })
    }
  }
}
</script>

<style scoped>

</style>