<template>
  <v-card>
    <v-card-title>Выбрать технику компании</v-card-title>
    <v-card-text>
      <VehicleTypePicker v-model="selectedGroups"/>
    </v-card-text>
    <v-card-actions v-if="modal">
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
      <v-spacer/>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import VehicleTypePicker from "@/components/Common/VehicleTypePicker";

export default {
  name: "VehicleTypeForm",
  components: {VehicleTypePicker},
  props: ['value', 'modal'],
  data() {
    return {
      selectedGroups: this.value.map(i => i.types.map(j => j.id)).flat(),
    }
  },
  watch: {
    selectedGroups: {
      handler() {
        this.$emit("input", this.selectedGroups);
      }, deep: true,
    }
  },
  methods: {
    save() {
      this.$http.post(`companies/${this.$route.params.id}/set-vehicles`, {
        vehicle_types_id: this.selectedGroups,
      }).then(() => {
        this.$emit('close');
        this.$emit('updated')
      })
    }
  }
}
</script>

<style scoped>

</style>