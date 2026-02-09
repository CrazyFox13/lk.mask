<template>
  <v-card>
    <v-card-actions>
      <v-spacer/>
      <v-btn small icon color="warning" @click="edit=true" v-if="!edit">
        <v-icon>mdi-pencil</v-icon>
      </v-btn>
      <v-btn small icon color="error" @click="edit=false" v-if="edit">
        <v-icon>mdi-close</v-icon>
      </v-btn>
      <v-btn small icon color="success" @click="save()" v-if="edit">
        <v-icon>mdi-check</v-icon>
      </v-btn>
    </v-card-actions>
    <v-list>
      <v-list-item>
        <v-list-item-subtitle>Описание</v-list-item-subtitle>
        <v-list-item-title >
          <v-textarea
              v-if="edit"
              v-model="order.description"
              label="Введите описание"
              :error="!!errors.description"
              :error-count="1"
              :error-messages="errors.description"
          />
          <span v-else style="white-space: normal">{{ order.description }}</span>
        </v-list-item-title>
      </v-list-item>
      <v-list-item v-if="order.documents&&order.documents.length">
        <v-list-item-subtitle>Документы</v-list-item-subtitle>
        <v-list-item-title>
          <v-chip-group column>
            <v-chip v-for="document in order.documents" :key="document.id" :href="document.url" target="_blank">
              {{ document.type }}-{{ document.id }}
            </v-chip>
          </v-chip-group>
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script>
import Swal from "sweetalert2-khonik";

export default {
  name: "OrderDetails",
  props: ['value'],
  data() {
    return {
      order: this.value,
      edit: false,
      errors: {}
    }
  },
  watch: {
    order: {
      handler() {
        this.$emit("input", this.order);
      }, deep: true,
    }
  },
  methods: {
    save() {
      this.errors = {};
      this.$http.post(`orders/${this.order.id}/set-details`,this.order).then(() => {
        Swal.fire('Данные сохранены').then(() => {
          this.edit = false;
        })
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>