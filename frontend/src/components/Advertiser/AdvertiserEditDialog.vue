<template>
  <v-card>
    <v-card-title>Редактирование рекламодателя</v-card-title>
    <v-card-text>
      <v-text-field
          label="Название"
          v-model="advertiser.name"
          :error-messages="errors.name"
          :error-count="1"
          :error="!!errors.name"
      />
      <v-textarea
          label="Описание"
          v-model="advertiser.description"
          :error-messages="errors.description"
          :error-count="1"
          :error="!!errors.description"
      />
      <v-text-field
          label="ИНН"
          v-model="advertiser.inn"
          :error-messages="errors.inn"
          :error-count="1"
          :error="!!errors.inn"
      />
      <v-switch
          v-model="advertiser.is_active"
          label="Показывать"
      />
      <DatePicker
          v-model="advertiser.start_date"
          label="Начало показа"
          :error="errors.start_date"
      />
      <DatePicker
          v-model="advertiser.end_date"
          label="Завершение показа"
          :error="errors.end_date"
      />
    </v-card-text>
    <v-card-actions>
      <v-btn text @click="$emit('close')">Закрыть</v-btn>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>

import DatePicker from "@/components/Common/Datepicker";

export default {
  name: "AdvertiserEditDialog",
  components: {DatePicker},
  props: ['value'],
  data() {
    return {
      advertiser: this.value,
      errors: {}
    }
  },
  created() {
    if (!this.advertiser.id) {
      this.advertiser.is_active = true;
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.advertiser.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`advertisers`, this.advertiser).then(r => {
        this.$emit("created", r.body.advertiser);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`advertisers/${this.advertiser.id}`, this.advertiser).then(r => {
        this.$emit("updated", r.body.advertiser);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>