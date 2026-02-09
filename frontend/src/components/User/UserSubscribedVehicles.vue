<template>
  <div>
    <div class="d-flex align-items-center justify-space-between">
      <div class="text-subtitle-1">Подписки на технику</div>
      <v-btn small color="primary" @click="dialog=true">Изменить</v-btn>
    </div>
    <div>
      <v-chip class="mr-2" v-for="(vehicle,i) in value" :key="i">{{ vehicle.title }}</v-chip>
    </div>

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>Обновить подписку</v-card-title>
        <v-card-text>
          <VehicleTypePicker
              v-if="dialog"
              style="max-height: 400px;overflow-y:auto"
              v-model="vehicle_types_id"

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
import VehicleTypePicker from "@/components/Common/VehicleTypePicker";

export default {
  name: "UserSubscribedVehicles",
  components: {VehicleTypePicker},
  props: ['subscribed','user'],
  data() {
    return {
      dialog: false,
      value:this.subscribed,
      vehicle_types_id: [],
    }
  },
  created() {
    this.vehicle_types_id = this.subscribed.map(s => s.id);
  },
  methods: {
    save() {
      this.$http.post(`users/${this.user.id}/subscribe-vehicles`, {
        vehicle_types_id: this.vehicle_types_id,
      }).then(({body})=>{
        this.value = body.subscribed;
        this.dialog=false;
      })
    }
  }
}
</script>

<style scoped>

</style>