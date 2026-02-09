<template>
  <v-list>
    <v-list-item v-for="address in addresses" :key="address.id">
      <v-list-item-content>
        <v-list-item-subtitle>{{address.region?address.region.name:''}} {{address.city?address.city.name:''}}</v-list-item-subtitle>
        <v-list-item-title>{{ address.address }}</v-list-item-title>
      </v-list-item-content>
    </v-list-item>
  </v-list>
</template>

<script>
export default {
  name: "OrderAddresses",
  props: ['addresses']
}
</script>

<style scoped>

</style>