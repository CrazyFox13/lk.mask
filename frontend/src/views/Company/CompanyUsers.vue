<template>
  <div>
    <div class="d-flex justify-space-between mb-2">

      <div class="text-subtitle-1">Сотрудники</div>

      <v-btn small @click="create()" color="primary">Добавить сотрудника</v-btn>
    </div>
    <v-list v-if="totalCount>0">
      <v-list-item v-for="user in users" :key="user.id">
        <v-list-item-title>
          <v-btn text :to="`/users/${user.id}`">{{ user.name }} {{ user.surname }}</v-btn>
        </v-list-item-title>
      </v-list-item>
      <v-pagination v-model="page" :length="pagesCount"/>
    </v-list>
    <v-alert v-else type="info" outlined>В компании нет сотрудников</v-alert>

    <v-dialog v-model="createDialog" max-width="500">
      <UserCreateDialog
          @close="createDialog=false"
          v-model="newUser"
          @created="onCreated"
      />
    </v-dialog>
  </div>
</template>

<script>
import UserCreateDialog from "@/components/User/UserCreateDialog.vue";

export default {
  name: "CompanyUsers",
  components: {UserCreateDialog},
  props: ['company_id'],
  data() {
    return {
      users: [],
      pagesCount: 1,
      page: 1,
      totalCount: 0,

      createDialog: false,
      newUser: {
        name: "",
        surname: "",
        phone: "",
        email: "",
        company_id: this.company_id,
        company_role: "employee"
      }
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
      this.$http.get(`users?take=5&company_id=${this.company_id}&page=${this.page}&status=staff`).then(r => {
        this.users = r.body.users;
        this.pagesCount = r.body.pagesCount;
        this.totalCount = r.body.totalCount;
      })
    },
    create() {
      this.newUser = {
        name: "",
        surname: "",
        phone: "",
        email: "",
        company_id: this.company_id,
        company_role: "employee"
      }
      ;
      this.createDialog = true;
    },
    onCreated() {
      this.getUsers();
    },
  }
}
</script>

<style scoped>

</style>