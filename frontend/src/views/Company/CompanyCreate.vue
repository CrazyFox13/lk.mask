<template>
  <div>
    <v-breadcrumbs
        :items="breadcrumps"
        divider="-"
    />
    <h5 class="text-h5">Создать компанию</h5>
    <v-row>
      <v-col cols="12" md="6">
        <CompanyForm ref="form" v-model="company"/>
      </v-col>
      <v-col cols="12" md="6">
        <VehicleTypeForm
            v-model="company.vehicle_types_id"
        />
      </v-col>
    </v-row>
  </div>
</template>

<script>
import CompanyForm from "@/components/Company/CompanyForm";
import VehicleTypeForm from "@/views/Company/VehicleTypeForm";

export default {
  name: "CompanyCreate",
  components: {VehicleTypeForm, CompanyForm},
  data() {
    return {
      company: {
        vehicle_types_id: []
      }
    }
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Компании',
          disabled: false,
          href: '/admin/companies',
        },
        {
          text: `Создать компанию`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    onTypesInput(v) {
      this.$set(this.company, 'vehicle_types_id', v);
    }
  }
}
</script>

<style scoped>

</style>