<template>
  <v-menu :close-on-content-click="false" class="white" max-height="500">
    <template v-slot:activator="{ on, attrs }">
      <v-btn plain text v-bind="attrs" v-on="on">
        Техника
        ({{ selectedValue && selectedValue.length }})
      </v-btn>
    </template>
    <v-list shaped>
      <v-list-item-group v-model="vehicle_types_id" multiple>
        <v-list-item v-if="noVehicleValue">
          <template v-slot:default="{  }">
            <v-list-item-content>
              <v-list-item-title>Техника не выбрана</v-list-item-title>
            </v-list-item-content>
            <v-list-item-action>
              <v-checkbox :value="-1" v-model="notSelected"/>
            </v-list-item-action>
          </template>
        </v-list-item>
        <template v-for="(group, i) in vehicleGroups">
          <v-subheader :key="`divider-${i}`">{{ group.title }}</v-subheader>
          <v-list-item v-for="type in group.types" :key="`item-${i}-${type.id}`" :value="type.id">
            <template v-slot:default="{ active }">
              <v-list-item-content>
                <v-list-item-title v-text="type.title"></v-list-item-title>
              </v-list-item-content>
              <v-list-item-action>
                <v-checkbox :input-value="active"/>
              </v-list-item-action>
            </template>
          </v-list-item>
        </template>
      </v-list-item-group>
    </v-list>
  </v-menu>
</template>

<script>
export default {
  name: "VehicleTypeFilter",
  props: ['value', 'noVehicleValue'],
  data() {
    return {
      vehicleGroups: [],
      vehicle_types_id: this.value ? this.value : [],
      notSelected: false,
    }
  },
  created() {
    this.getVehicleGroups();
  },
  computed: {
    selectedValue() {
      return this.vehicle_types_id.filter(t => t !== 0);
    }
  },
  watch: {
    vehicle_types_id: {
      handler(value) {
        const selected = value.filter(v => v > 0).length > 0;
        if (selected) {
          this.notSelected = false;
        }
        this.$emit("input", this.selectedValue)
      },
      deep: true
    },
    value: {
      handler(v,ov) {
        if(JSON.stringify(v) !== JSON.stringify(ov)) {
          this.vehicle_types_id = v;
        }
      }, deep: true
    },
    notSelected() {
      if (this.notSelected) {
        this.vehicle_types_id = [-1];
      } else {
        const idx = this.vehicle_types_id.indexOf(-1);
        if (idx >= 0) this.vehicle_types_id.splice(idx, 1);
      }
      this.$emit("input", this.selectedValue)
    }
  },
  methods: {
    getVehicleGroups() {
      this.$http.get(`vehicle-groups`).then(r => {
        this.vehicleGroups = r.body.vehicleGroups;
      })
    }
  }
}
</script>

<style scoped>

</style>