<template>
  <v-card>
    <v-card-title>Редактирование наград</v-card-title>
    <v-card-text>
      <file-uploader
          label="Иконка"
          v-model="award.icon"
          :error="errors.icon"
      >
        <template #prepend-inner>
          <v-img width="40" height="40" v-if="award.icon" :src="award.icon"/>
        </template>
      </file-uploader>
      <v-text-field
          label="Название"
          v-model="award.name"
          :error-messages="errors.name"
          :error-count="1"
          :error="!!errors.name"
      />
      <v-textarea
          label="Описание"
          v-model="award.description"
          :error-messages="errors.description"
          :error-count="1"
          :error="!!errors.description"
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
import FileUploader from "@/components/Common/FileUploader";
export default {
  name: "AwardEditDialog",
  components: {FileUploader},
  props: ['value'],
  data() {
    return {
      award: this.value,
      errors: {}
    }
  },
  methods: {
    save() {
      this.errors = {};
      if (this.award.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`awards`, this.award).then(r => {
        this.$emit("created", r.body.award);
        this.$emit("close");
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`awards/${this.award.id}`, this.award).then(r => {
        this.$emit("updated", r.body.award);
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