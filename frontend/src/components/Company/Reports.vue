<template>
  <div>
    <v-list v-if="reports.length>0">
      <v-list-item v-for="report in reports" :key="report.id">
        <v-list-item-content>
          <v-list-item-title>{{ report.text }}</v-list-item-title>
          <v-list-item-subtitle>{{ moment(report.created_at).format('HH:mm DD.MM.YYYY') }}</v-list-item-subtitle>
        </v-list-item-content>
        <v-list-item-icon>
          <v-icon v-if="report.status==='draft'">mdi-progress-pencil</v-icon>
          <v-icon color="warning" v-if="report.status==='active'">mdi-alert-outline</v-icon>
          <v-icon color="error" v-if="report.status==='referee'">mdi-account-alert-outline</v-icon>
          <v-icon v-if="report.status==='canceled'">mdi-cancel</v-icon>
          <v-icon color="success" v-if="report.status==='resolved'">mdi-check-all</v-icon>
        </v-list-item-icon>
        <v-list-item-action>
          <v-btn small text :to="`/reports/${report.id}`">Открыть</v-btn>
        </v-list-item-action>
      </v-list-item>
      <v-pagination v-model="page" :length="pagesCount"/>
    </v-list>
    <v-alert outlined type="info" v-else>Жалобы не найдены</v-alert>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "CompanyReports",
  props: ['company_id'],
  data() {
    return {
      page: 1,
      pagesCount: 1,
      reports: [],
      moment:moment,
    }
  },
  created() {
    this.getReports();
  },
  watch: {
    page() {
      this.getReports();
    }
  },
  methods: {
    getReports() {
      this.$http.get(`reports?take=5&page=${this.page}&filter=to_company&company_id=${this.company_id}`).then(r => {
        this.reports = r.body.reports;
        this.pagesCount = r.body.pagesCount;
      })
    }
  }
}
</script>

<style scoped>

</style>