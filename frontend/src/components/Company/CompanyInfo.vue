<template>
  <v-list>
    <v-list-item v-if="company.boss">
      <v-list-item-subtitle>Создатель</v-list-item-subtitle>
      <v-list-item-title>
        <v-btn text :to="`/users/${company.boss.id}`">
          {{ getBossName(company.boss) }}
        </v-btn>
      </v-list-item-title>
    </v-list-item>
    <v-list-item style="align-items: flex-start;">
      <v-list-item-subtitle>
        Техника
        <v-btn color="primary" class="ml-2" icon small @click="vehicleTypeDialog=true">
          <v-icon>mdi-pencil</v-icon>
        </v-btn>
      </v-list-item-subtitle>
      <v-list-item-title>
        <v-chip-group column v-for="group in companyVehicleGroups" :key="group.id">
          <v-chip
              v-for="type in group.types"
              :key="type.id"
          >{{ type.title }}
          </v-chip>
          <!--<v-chip color="info" @click="vehicleTypeDialog=true">
            <v-icon small>mdi-plus</v-icon>
            Добавить
          </v-chip>-->
        </v-chip-group>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Документы</v-list-item-subtitle>
      <v-list-item-title>
        <v-chip-group column>
          <v-chip :key="document.id" v-for="document in company.documents"
                  :href="document.url" target="_blank">{{ document.type }}-{{ document.id }}
          </v-chip>
        </v-chip-group>
      </v-list-item-title>
    </v-list-item>
    <v-list-item>
      <v-list-item-subtitle>Рейтинг</v-list-item-subtitle>
      <v-list-item-title>
        <v-rating size="14" half-increments readonly :length="5" :value="company.rating"/>
      </v-list-item-title>
    </v-list-item>

    <v-dialog v-model="vehicleTypeDialog" :max-width="500">
      <VehicleTypeForm
          :value="companyVehicleGroups"
          @close="vehicleTypeDialog=false"
          @updated="$emit('updated')"
          :modal="true"
      />
    </v-dialog>
  </v-list>
</template>

<script>
import VehicleTypeForm from "@/views/Company/VehicleTypeForm";

export default {
  name: "CompanyInfo",
  components: {VehicleTypeForm},
  props: ['company'],
  data() {
    return {
      companyVehicleGroups: this.company.vehicle_groups,
      vehicleTypeDialog: false,
    }
  },
  methods: {
    getBossName(boss) {
      if (!boss) return "";
      const name = [boss.name, boss.surname].join(" ").trim();
      if (name) return name;
      return "Имя не указано";
    }
  }
}
</script>

<style scoped>

</style>