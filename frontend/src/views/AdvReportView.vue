<template>
  <v-row class="mx-3 mt-5">
    <v-col cols="12">
      <div class="text-h6">Отчёт</div>

      <v-card class="mt-3">
        <v-card-text>
          <p class="mt-4 mb-1">Выберите анализируемую сущность</p>
          <v-select
              label="Группировка"
              v-model="groupValue"
              :items="groupOptions"
              item-text="text"
              item-value="value"
              :error="!!errors.group"
              :error-messages="errors.group"
              :error-count="1"
          />

          <p class="mt-4 mb-1">Установите фильтры</p>
          <v-row>
            <v-col cols="12" sm="6" md="4">
              <DatePicker
                  label="Дата от"
                  v-model="query.date_from"
                  :error="errors.date_from"
              />
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <DatePicker
                  label="Дата до"
                  v-model="query.date_to"
                  :error="errors.date_to"
              />
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field
                  readonly
                  @click="dialog=true"
                  label="Техника"
                  :value="vehicleString"
              />
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <AdvPlacePicker
                  v-model="query.adv_place_id"
              />
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <AdvertiserPicker
                  v-model="query.advertiser_id"
              />
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <AdvBannerPicker
                  v-model="query.adv_banner_id"
                  :advertiser_id="query.advertiser_id"
                  :place_id="query.adv_place_id"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="submit()">Сформировать отчёт</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>


    <v-col cols="12">
      <v-card>
        <v-card-title>
          Данные отчёта
        </v-card-title>
        <v-card-text>
          <v-simple-table>
            <template #default>
              <thead>
              <tr>
                <th class="text-left">{{ selectedGroup?.text }}</th>
                <th class="text-left">Онлайн</th>
                <th class="text-left">Показы</th>
                <th class="text-left">Переходы</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(row,k) in data" :key="k">
                <td>{{ row.value }}</td>
                <td>{{ row.online_users }}</td>
                <td>{{ row.views_count }}</td>
                <td>{{ row.clicks_count }}</td>
              </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card-text>
        <v-card-actions>
          <v-btn color="success" v-if="data.length>0" @click="exportData()">
            <v-icon>mdi-microsoft-excel</v-icon>
            &nbsp;
            Excel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-col>
    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>Выберите технику</v-card-title>
        <v-card-text>
          <VehicleTypePicker
              v-model="query.vehicle_types_id"
              @updated="onTypesChanged"
          />
        </v-card-text>
        <v-card-actions>
          <v-btn @click="dialog=false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import DatePicker from "@/components/Common/Datepicker";
import AdvertiserPicker from "@/components/Common/AdvertiserPicker";
import VehicleTypePicker from "@/components/Common/VehicleTypePicker";
import AdvBannerPicker from "@/components/Common/AdvBannerPicker";
import AdvPlacePicker from "@/components/Common/AdvPlacePicker";

export default {
  name: "AdvReportView",
  components: {AdvPlacePicker, AdvBannerPicker, VehicleTypePicker, AdvertiserPicker, DatePicker},
  data() {
    return {
      dialog: false,
      groupValue: undefined,
      groupOptions: [
        {value: 'day', text: 'День'},
        {value: 'month', text: 'Месяц'},
        {value: 'advertiser', text: 'Рекламодатель'},
        {value: 'banner', text: 'Баннер'},
      ],
      query: {},
      vehicleString: '',
      data: {},
      errors: {},
    }
  },
  computed: {
    selectedGroup() {
      return this.groupOptions.find(x => x.value === this.groupValue);
    }
  },
  methods: {
    exportData() {
      /*this.$http.post(`adv-report/export`, {
        group: this.groupValue,
        ...this.query
      }).then(response => {
        return response.blob();
        //   window.location.href = b;
      }).then(r => {
        const blob = new Blob([r])
        const href = URL.createObjectURL(blob);

        // create "a" HTML element with href to file & click
        const link = document.createElement('a');
        link.href = href;
        link.setAttribute('download', 'export.xlsx'); //or any other extension
        document.body.appendChild(link);
        link.click();

        // clean up "a" element & remove ObjectURL
        document.body.removeChild(link);
        URL.revokeObjectURL(href);
      })*/

      //adv-report/export
      const query = new URLSearchParams({
        group: this.groupValue,
        ...this.query
      }).toString()

      window.location.href = `${process.env.VUE_APP_API_ENDPOINT}/api/adv-report/export?${query}`
    },
    submit() {
      this.errors = {};
      this.$http.post(`adv-report`, {
        group: this.groupValue,
        ...this.query
      }).then(body => {
        this.data = body.data.data;
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    onTypesChanged(models) {
      this.vehicleString = models.map(i => i.title).join(", ")
    }
  }
}
</script>

<style scoped>

</style>