<template>
  <div>
    <div class="d-flex align-items-center justify-space-between">
      <div class="text-subtitle-1">Подписки на города</div>
      <v-btn small color="primary" @click="dialog=true">Изменить</v-btn>
    </div>
    <div class="mt-2">
      <v-chip class="mr-2 mb-1" v-for="(city,i) in value" :key="i">{{ city.name }}</v-chip>
    </div>

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>Обновить подписку</v-card-title>
        <v-card-text>
          <CityPicker
              v-if="dialog"
              style="max-height: 400px;overflow-y:auto"
              v-model="cities_id"
          />
        </v-card-text>
        <v-card-actions>
          <v-btn @click="dialog=false">Закрыть</v-btn>
          <v-spacer/>
          <v-btn color="primary" @click="save()">Сохранить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>

</template>

<script>
import CityPicker from "@/components/Common/CityPicker";

export default {
  name: "UserSubscribedCities",
  components: {CityPicker},
  props: ['subscribed', 'user'],
  data() {
    return {
      dialog: false,
      value: this.subscribed,
      cities_id: []
    }
  },
  created() {
    this.cities_id = this.subscribed.map(c => c.id);
  },
  methods: {
    save() {
      this.$http.post(`users/${this.user.id}/subscribe-cities`, {
        geo_cities_id: this.cities_id,
      }).then(({body}) => {
        this.value = body.subscribed;
        this.dialog = false;
      })
    }
  }
}
</script>

<style scoped>

</style>