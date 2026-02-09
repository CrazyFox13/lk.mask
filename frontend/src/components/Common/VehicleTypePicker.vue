<template>
  <v-list-item-group multiple>
    <v-list-group no-action v-for="(group) in groups" :key="group.id">
      <template v-slot:activator>
        <v-list-item-title>
          {{ group.title }} ({{ selectedCount(group) }}/{{ group.types.length }})
        </v-list-item-title>
      </template>
      <v-list-item v-for="type in group.types" :key="type.id" link>
        <template v-slot:default="{  }">
          <v-list-item-action>
            <v-checkbox v-model="selected" :value="type.id"/>
          </v-list-item-action>
          <v-list-item-title>{{ type.title }}</v-list-item-title>
        </template>
      </v-list-item>
    </v-list-group>
  </v-list-item-group>
</template>

<script>
export default {
  name: "VehicleTypePicker",
  props: ['value'],
  data() {
    return {
      selected: this.value ? this.value : [],
      groups: []
    }
  },
  created() {
    this.$http.get(`vehicle-groups`).then(({body}) => {
      this.groups = body.vehicleGroups;
    })
  },
  watch: {
    selected: {
      handler() {
        this.$emit('input', this.selected);
        this.$emit('updated', this.selectedTypes)
        this.$emit("vehicleStringChanged", this.selectedString)
      }, deep: true
    },
  },
  computed: {
    selectedTypes() {
      return this.groups.map(i => i.types.filter(x => this.selected.includes(x.id))).flat();
    },
    selectedString() {
      return this.selectedTypes.map(t => t.title).join(", ");
    }
  },
  methods: {
    selectedCount(group) {
      let i = 0;
      for (let type of group.types) {
        if (this.selected.includes(type.id)) {
          i++
        }
      }
      return i;
    },
  }
}
</script>

<style scoped>

</style>