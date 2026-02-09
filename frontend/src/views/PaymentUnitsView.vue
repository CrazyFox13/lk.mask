<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="d-flex align-items-center">
        <div class="text-h6">Типы оплат</div>
        <v-spacer/>
        <v-btn small @click="create()" color="primary">Добавить тип оплаты</v-btn>
      </div>
    </v-col>
    <v-col cols="12">
      <v-data-table
          :headers="headers"
          :items="items"
          :options="options"
          :loading="loading"
          class="elevation-1 mt-3"
          disable-pagination
      >
        <template v-slot:[`item.actions`]="{item}">
          <v-btn icon color="warning" @click="edit(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon color="error" class="ml-2" @click="destroy(item)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-col>

    <v-dialog v-model="editDialog" max-width="500">
      <PaymentUnitEditDialog
          v-if="editDialog"
          @close="editDialog=false"
          v-model="editItem"
          @created="onCreated"
          @updated="onUpdated"
      />
    </v-dialog>
  </v-row>
</template>

<script>
import ResourceComponentHelper from "@/mixins/ResourceComponentHelper";
import PaymentUnitEditDialog from "@/components/PaymentUnit/PaymentUnitEditDialog";

export default {
  name: "PaymentUnitsView",
  components: {PaymentUnitEditDialog},
  mixins: [ResourceComponentHelper],
  data() {
    return {
      headers: [
        {text: "ID", value: "id", sortable: true},
        {text: "Название", value: "name", sortable: true},
        {text: "Кол-во типов техники", value: "vehicle_types_count", sortable: true},
        {text: "", value: "actions", sortable: false},
      ],
      resourceKey: "paymentUnits",
      resourceApiRoute: `payment-units`,
      deleteSwalTitle: "Вы действительно хотите удалить вид платежа?"
    }
  },
}
</script>

<style scoped>

</style>