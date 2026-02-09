<template>
  <div>
    <v-simple-table>
      <template v-slot:default>
        <thead>
        <tr>
          <th class="text-left">
            ID
          </th>
          <th class="text-left">
            Имя
          </th>
          <th class="text-left">
            Контакты
          </th>
          <th class="text-left">
            Компания
          </th>
          <th class="text-left">
            Устройство
          </th>
          <th class="text-left">
            Отправлено
          </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>
            <v-avatar
                size="36px"
                class="mr-2"
                color="teal"
            >
              <img
                  v-if="user.avatar"
                  alt="Avatar"
                  :src="user.avatar"
              >
              <span class="white--text" v-else>{{ avaLetters(user) }}</span>
            </v-avatar>
            {{ user.name }} {{ user.surname }}
          </td>
          <td>{{ [user.phone, user.email].join(', ') }}</td>
          <td>
            {{ user.company?.title }}
          </td>
          <td>
            {{ user.device?.type }}
          </td>

          <td>
            {{ user.pivot.sent_at ? moment(user.pivot.sent_at).format("HH:mm DD.MM.YYYY") : 'Не отправлено' }}
          </td>
        </tr>
        </tbody>
      </template>
    </v-simple-table>
    <v-pagination v-model="page" :length="pagesCount"/>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "NotificationUsers",
  props: ['notification', 'schedule_id'],
  data() {
    return {
      moment: moment,
      page: 1,
      pagesCount: 1,
      users: [],
    }
  },
  created() {
    this.getUsers();
  },
  watch: {
    page() {
      this.getUsers();
    }
  },
  methods: {
    getUsers() {
      this.$http.get(`push-notifications/${this.notification.id}/users?page=${this.page}&schedule_id=${this.schedule_id || ""}`).then(r => {
        this.users = r.body.users;
        this.pagesCount = r.body.pagesCount;
      });
    },
    avaLetters(user) {
      let surnameLetter = user.surname ? user.surname[0] : '';
      let nameLetter = user.name ? user.name[0] : '';
      return `${surnameLetter}${nameLetter}`
    },
  }
}
</script>

<style scoped>

</style>