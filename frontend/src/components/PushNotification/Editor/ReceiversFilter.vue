<template>
  <v-row>
    <v-col cols="12" md="4">
      <v-text-field
          label="Поиск"
          hide-details
          v-model="query.search"
          dense
      />
    </v-col>
    <v-col cols="12" md="4">
      <v-select
          dense
          label="Статус"
          v-model="query.status"
          hide-details
          :items="[{key:'',value:'Все'},{key:'without_company',value:'Без компании'},{key:'boss',value:'Администратор компании'},{key:'staff',value:'Сотрудник компании'},]"
          item-value="key"
          item-text="value"
      />
    </v-col>
    <v-col cols="12" md="4">
      <v-select
          dense
          label="Тип устройства"
          v-model="query.devices"
          hide-details
          :items="[{key:'web',value:'Web'},{key:'ios',value:'iOs'},{key:'android',value:'Android'}]"
          item-value="key"
          item-text="value"
          multiple
      />
    </v-col>

    <v-col cols="12" md="6">
      <CompanyTypePicker
          v-model="query.company_types_id"
      />
    </v-col>
    <v-col cols="12" md="6">
      <v-checkbox
          label="Незаполненный профиль"
          v-model="query.without_profile"
          hide-details
          dense
      />
    </v-col>
    <v-col cols="12" md="6">
      <v-select
          label="Статус компании"
          v-model="query.company_status"
          hide-details
          dense
          item-value="key"
          item-text="value"
          :items="[{key:'',value:'Все'},...companyModerationStatuses]"
      />
    </v-col>
    <v-col cols="12" md="6">
      <v-checkbox
          label="Нет сохраненных фильтров"
          v-model="query.without_filters"
          hide-details
          dense
      />
    </v-col>
    <v-col cols="12" md="6">
      <v-checkbox
          label="Не получает уведомления на технику"
          v-model="query.without_vehicle_subscribe"
          hide-details
          dense
      />
    </v-col>
    <v-col cols="12" md="6">
      <v-checkbox
          v-if="pushNotification.type==='push'"
          label="Подписан на персональные сообщения"
          v-model="query.subscribed_personal_push"
          hide-details
          dense
      />
      <v-checkbox
          v-if="pushNotification.type==='email'"
          label="Подписан на персональные сообщения"
          v-model="query.subscribed_personal_email"
          hide-details
          dense
      />

    </v-col>
    <!--<v-col cols="12" md="4" class="d-flex align-center">
      <v-btn small color="primary" @click="search">Найти</v-btn>
    </v-col>-->
  </v-row>
</template>

<script>
import CompanyTypePicker from "@/components/Common/CompanyTypePicker";

export default {
  name: "ReceiversFilter",
  components: {CompanyTypePicker},
  props: ['value', 'pushNotification'],
  data() {
    return {
      query: this.value,
      companyTypes: [],
    }
  },
  methods: {
    search() {
      this.$emit('input', this.query);
    }
  }
}
</script>

<style scoped>

</style>