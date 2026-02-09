<template>
  <div>
    <v-list v-if="recommendations.length>0">
      <v-list-item v-for="recommendation in recommendations" :key="recommendation.id">
        <v-list-item-content>
          <v-list-item-title>{{ recommendation.text }}</v-list-item-title>
          <v-list-item-subtitle>
            {{moment(recommendation.created_at).format('HH:mm DD.MM.YYYY') }}
          </v-list-item-subtitle>
        </v-list-item-content>
        <v-list-item-icon>
          <v-icon color="warning" v-if="recommendation.status==='draft'">mdi-alert-outline</v-icon>
          <v-icon color="success" v-if="recommendation.status==='approved'">mdi-check-all</v-icon>
          <v-icon v-if="recommendation.status==='canceled'">mdi-cancel</v-icon>
        </v-list-item-icon>
        <!--<v-list-item-action>
          <v-btn small text :to="`/recommendations/${recommendation.id}`">Открыть</v-btn>
        </v-list-item-action>-->
      </v-list-item>
      <v-pagination v-model="page" :length="pagesCount"/>
    </v-list>
    <v-alert outlined type="info" v-else>Рекомендации не найдены</v-alert>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "CompanyRecommendations",
  props: ['company_id'],
  data() {
    return {
      page: 1,
      pagesCount: 1,
      recommendations: [],
      moment: moment,
    }
  },
  created() {
    this.getRecommendations();
  },
  watch: {
    page() {
      this.getRecommendations();
    }
  },
  methods: {
    getRecommendations() {
      this.$http.get(`recommendations?take=5&page=${this.page}&filter=to_company&company_id=${this.company_id}`).then(r => {
        this.recommendations = r.body.recommendations;
        this.pagesCount = r.body.pagesCount;
      })
    }
  }
}
</script>

<style scoped>

</style>