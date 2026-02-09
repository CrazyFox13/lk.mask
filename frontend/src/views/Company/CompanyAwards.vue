<template>
  <v-select
      label="Выберите награды"
      multiple
      :items="awards"
      item-value="id"
      item-text="name"
      @change="onChange"
      v-model="companyAwards"
  >
    <template v-slot:selection="{ item }">
      <v-chip label>
        <v-img :src="item.icon" width="20" height="20" class="mr-1"/>
        {{ item.name }}
      </v-chip>
    </template>
  </v-select>
</template>

<script>
export default {
  name: "CompanyAwards",
  props: ['company_id'],
  data() {
    return {
      awards: [],
      companyAwards: []
    }
  },
  created() {
    this.getAwards();
    this.getCompanyAwards();
  },
  methods: {
    getAwards() {
      this.$http.get(`awards`).then(({body}) => {
        this.awards = body.awards;
      })
    },
    getCompanyAwards() {
      this.$http.get(`awards?company_id=${this.company_id}`).then(({body}) => {
        this.companyAwards = body.awards;
      })
    },
    onChange() {
      this.$http.post(`companies/${this.company_id}/awards`, {
        awards: this.companyAwards
      });
    }
  }
}
</script>

<style scoped>

</style>