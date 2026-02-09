<template>
  <v-list>
    <v-list-item>
      <v-list-item-subtitle>Название</v-list-item-subtitle>
      <v-list-item-title>{{ company.title }}</v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Описание</v-list-item-subtitle>
      <v-list-item-title>{{ company.description }}</v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>E-mail</v-list-item-subtitle>
      <v-list-item-title>{{ company.email }}</v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Телефон</v-list-item-subtitle>
      <v-list-item-title>{{ company.phone }}</v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Сайт</v-list-item-subtitle>
      <v-list-item-title>
        <a target="_blank" :href="company.website">{{ company.website }}</a>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Рейтинг</v-list-item-subtitle>
      <v-list-item-title>
        <v-rating :value="company.rating" half-increments :length="5" readonly size="14"/>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-btn :to="`/companies/${company.id}`" small color="info">Открыть компанию</v-btn>
    </v-list-item>
  </v-list>
</template>

<script>
export default {
  name: "OrderCompany",
  props: ['company']
}
</script>

<style scoped>

</style>