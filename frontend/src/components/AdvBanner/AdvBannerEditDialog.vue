<template>
  <v-card>
    <v-card-title>Редактирование баннера</v-card-title>
    <v-card-text>
      <AdvertiserPicker
          v-model="banner.advertiser_id"
          :error="errors.advertiser_id"
      />

      <AdvPlacePicker
          v-model="banner.adv_place_id"
          :error="errors.adv_place_id"
          @selected="v=>selectedPlace= v"
      />
      <VehicleTypePicker
          v-if="selectedPlace && selectedPlace.with_vehicle_filter"
          v-model="banner.vehicle_types_id"
      />
      <v-text-field
          label="Название"
          v-model="banner.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />
      <v-textarea
          label="Подсказка (tooltip)"
          v-model="banner.tooltip"
          :error-messages="errors.tooltip"
          :error-count="1"
          :error="!!errors.tooltip"
      />
      <v-switch
          v-model="banner.is_active"
          label="Показывать"
      />
      <DatePicker
          v-model="banner.start_date"
          label="Начало показа"
          :error="errors.start_date"
      />
      <DatePicker
          v-model="banner.end_date"
          label="Завершение показа"
          :error="errors.end_date"
      />
      <FileUploader
          label="Загрузите изображение"
          v-model="banner.img_url"
          :error="errors.img_url"
          :hint="selectedPlace?`Размеры изображения: ${selectedPlace.width}x${selectedPlace.height}`:''"
      />
      <v-img v-if="banner.img_url" :src="banner.img_url" height="160" width="160" contain/>
      <v-text-field
          label="Ссылка на переход"
          v-model="banner.endpoint_url"
          :error-messages="errors.endpoint_url"
          :error-count="1"
          :error="!!errors.endpoint_url"
          placeholder="https://..."
      />
      <v-text-field
          label="Токен ERID"
          v-model="banner.token"
          :error-messages="errors.token"
          :error-count="1"
          :error="!!errors.token"
          placeholder="Введите токен"
      />
      <v-textarea
          label="Комментарий"
          v-model="banner.comment"
          :error-messages="errors.comment"
          :error-count="1"
          :error="!!errors.comment"
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
import FileUploader from "@/components/Common/FileUploader";
import VehicleTypePicker from "@/components/Common/VehicleTypePicker";
import AdvertiserPicker from "@/components/Common/AdvertiserPicker";
import AdvPlacePicker from "@/components/Common/AdvPlacePicker";

export default {
  name: "AdvBannerEditDialog",
  components: {AdvPlacePicker, AdvertiserPicker, VehicleTypePicker, FileUploader, DatePicker},
  props: ['value'],
  data() {
    return {
      banner: this.value,
      errors: {},
      selectedPlace: undefined,
    }
  },
  created() {
    this.banner.vehicle_types_id = this.banner?.vehicle_types?.map(i => i.id);
    if (!this.banner.id) {
      this.banner.is_active = true;
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.banner.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`adv-banners`, this.banner).then(r => {
        this.$emit("created", r.body.advBanner);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`adv-banners/${this.banner.id}`, this.banner).then(r => {
        this.$emit("updated", r.body.advBanner);
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