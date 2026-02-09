<template>
  <div>
    <v-text-field label="Поиск" v-model="search"/>
    <v-list-item-group multiple>
      <v-list-group no-action v-for="(region) in regions" :key="region.id">
        <template v-slot:activator>
          <v-list-item-action>
            <v-checkbox @click.stop="()=>{}" v-model="selected_regions_id" :value="region.id"/>
          </v-list-item-action>
          <v-list-item-title>
            {{ region.name_with_type }} ({{ selectedCitiesCount(region) }})
          </v-list-item-title>
        </template>
        <v-list-item v-for="city in region.cities" :key="city.id" link>
          <template v-slot:default="">
            <v-list-item-action>
              <v-checkbox v-model="cities_id" :value="city.id"/>
            </v-list-item-action>
            <v-list-item-title>{{ city.name }}</v-list-item-title>
          </template>
        </v-list-item>
      </v-list-group>
    </v-list-item-group>
  </div>
</template>

<script>
export default {
  name: "CityPicker",
  props: ['value', 'regions_id'],
  data() {
    return {
      cities_id: this.value ? this.value : [],
      regions: [],
      selected_regions_id: this.regions_id ? this.regions_id : [],
      search: "",
    }
  },
  created() {
    this.getCities();
  },
  watch: {
    cities_id: {
      handler() {
        this.$emit("input", this.cities_id)
      }, deep: true
    },
    selected_regions_id: {
      handler(newV, oldV) {
        const addedRegionId = newV.find(i => !oldV.includes(i));
        if (addedRegionId) {
          this.selectAllCitiesInRegion(addedRegionId);
        }

        const removedRegionId = oldV.find(i => !newV.includes(i));
        if (removedRegionId) {
          this.unSelectAllCitiesInRegion(removedRegionId);
        }
      }, deep: true
    },
    search: {
      handler(){
        this.getCities();
      },
    }
  },
  methods: {
    unSelectAllCitiesInRegion(regionId) {
      const region = this.regions.find(r => r.id === regionId);
      if (region) {
        region.cities.forEach(city => {
          if (this.cities_id.includes(city.id)) {
            const idx = this.cities_id.indexOf(city.id);
            this.cities_id.splice(idx, 1);
          }
        });
      }
    },
    selectAllCitiesInRegion(regionId) {
      const region = this.regions.find(r => r.id === regionId);
      if (region) {
        region.cities.forEach(city => {
          if (!this.cities_id.includes(city.id)) {
            this.cities_id.push(city.id);
          }
        });
      }
    },
    getCities() {
      this.$http.get(`geo-regions?take=-1&city=${this.search}`).then(({body}) => {
        this.regions = body.regions;
        if (this.regions_id) {
          this.regions_id.forEach(this.selectAllCitiesInRegion)
        }
        this.selectRegionsByCities();
      })
    },
    selectRegionsByCities() {
      this.regions.forEach(region => {
        const totalCitiesCount = region.cities.length;
        const regionCitiesId = region.cities.map(c => c.id);
        const selectedCitiesInThisRegion = this.cities_id.filter(i => regionCitiesId.includes(i));
        if (selectedCitiesInThisRegion.length && totalCitiesCount === selectedCitiesInThisRegion.length) {
          this.selected_regions_id.push(region.id);
        }
      })
    },
    selectedCitiesCount(region) {
      let count = 0;
      region.cities.forEach(city => {
        if (this.cities_id.includes(city.id)) {
          count++
        }
      });
      return count;
    }
  }
}
</script>

<style scoped>

</style>