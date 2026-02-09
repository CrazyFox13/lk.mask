<template>
  <v-card>
    <v-card-title>Редактирование фильтра</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="orderFilter.name"
          :error-messages="errors.name"
          :error-count="1"
          :error="!!errors.name"
      />
      <v-row>
        <v-col cols="12" :sm="6" :lg="4">
          <v-switch label="Отправлять по email" v-model="orderFilter.active_email" :true-value="1" :false-value="0"/>
        </v-col>
        <v-col cols="12" :sm="6" :lg="4">
          <v-switch label="Отправлять через PUSH" v-model="orderFilter.active_push" :true-value="1" :false-value="0"/>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" :sm="6" :lg="4">
          <div class="text-subtitle">Города</div>
          <CityPicker
              style="max-height: 400px;overflow-y:auto"
              v-model="query.cities_id"
              :regionsId="query.regions_id"
          />
        </v-col>
        <v-col cols="12" :sm="6" :lg="4">
          <div class="text-subtitle">Техника</div>
          <VehicleTypePicker
              style="max-height: 400px;overflow-y:auto"
              v-model="query.vehicle_types_id"
              @vehicleStringChanged="str=>orderFilter.name=str"

          />
        </v-col>

        <v-col cols="12" :sm="12" :lg="4">
          <v-row>
            <v-col cols="12" :sm="6" :md="3" :lg="4">
              <div class="text-subtitle">Сроки заказа</div>
              <v-radio-group v-model="query.shifts">
                <v-radio label="На одну смену" value="one"/>
                <v-radio label="На две смену" value="two"/>
                <v-radio label="До 5 смен" value="less_five"/>
                <v-radio label="Более 5 смен" value="more_five"/>
              </v-radio-group>
            </v-col>
            <v-col cols="12" :sm="6" :md="3" :lg="12">
              <div class="text-subtitle">Расчет</div>
              <v-checkbox
                  label="Договорная стоимость"
                  v-model="query.amount_by_agreement"
                  hide-details
                  dense
                  :true-value="1"
              />
              <v-checkbox
                  label="Стоимость с НДС"
                  v-model="query.amount_with_vat"
                  hide-details
                  dense
                  :true-value="1"
              />
              <v-checkbox
                  label="Наличный расчет"
                  v-model="query.amount_cash"
                  hide-details
                  dense
                  :true-value="1"
              />
            </v-col>
            <v-col cols="12" :sm="6" :md="3" :lg="12">
              <div class="text-subtitle">Дата начала работ</div>
              <DatePicker v-model="query.date"/>
            </v-col>
            <v-col cols="12" :sm="6" :md="3" :lg="12">
              <div class="text-subtitle">Прочее</div>
              <v-checkbox
                  label="Только от компаний"
                  v-model="query.with_company"
                  hide-details
                  dense
                  :true-value="1"
              />
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import VehicleTypePicker from "@/components/Common/VehicleTypePicker";
import CityPicker from "@/components/Common/CityPicker";
import DatePicker from "@/components/Common/Datepicker";

export default {
  name: "OrderFilterEditDialog",
  components: {DatePicker, CityPicker, VehicleTypePicker},
  props: ['value', 'user_id'],
  data() {
    return {
      orderFilter: this.value,
      errors: {},
      query: this.value.query ? JSON.parse(this.value.query) : {},
      nameChanged: false,
    }
  },
  created() {
    if(this.orderFilter.active_email === undefined){
      this.orderFilter.active_email=0;
    }
    if(this.orderFilter.active_push === undefined){
      this.orderFilter.active_push=0;
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.orderFilter.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`order-filters`, {
        user_id: this.user_id,
        ...this.orderFilter,
        query: JSON.stringify(this.query)
      }).then(r => {
        this.$emit("created", r.body.orderFilter);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`order-filters/${this.orderFilter.id}`, {
        ...this.orderFilter,
        query: JSON.stringify(this.query)
      }).then(r => {
        this.$emit("updated", r.body.orderFilter);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },

  }
}
</script>

<style scoped>

</style>