<template>
  <v-list>
    <v-list-item>
      <v-list-item-subtitle>Истец</v-list-item-subtitle>
      <v-list-item-title v-if="report.author">
        <v-btn text small :to="`/users/${report.author_user_id}`">
          {{ report.author.name }} {{ report.author.surname }}
        </v-btn>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Ответчик</v-list-item-subtitle>
      <v-list-item-title>
        <v-btn text small :to="`/users/${report.target_user_id}`" v-if="report.target_user">
          {{ report.target_user.name }} {{ report.target_user.surname }}
        </v-btn>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Компания</v-list-item-subtitle>
      <v-list-item-title>
        <v-btn text small :to="`/companies/${report.company_id}`" v-if="report.company">
          {{ report.company.title }}
        </v-btn>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Текст</v-list-item-subtitle>
      <v-list-item-title>{{ report.text }}</v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Сумма претензии</v-list-item-subtitle>
      <v-list-item-title>{{ report.amount }}</v-list-item-title>
    </v-list-item>
    <v-list-item v-if="report.documents && report.documents.length">
      <v-list-item-subtitle>Документы</v-list-item-subtitle>
      <v-list-item-title>
        <v-chip-group column>
          <v-chip :href="document.url" target="_blank" v-for="document in report.documents" :key="document.id">
            {{ document.type }}-{{ document.id }}
          </v-chip>
        </v-chip-group>
      </v-list-item-title>
    </v-list-item>
  </v-list>
</template>

<script>
export default {
  name: "ReportInfo",
  props: ['report']
}
</script>

<style scoped>

</style>