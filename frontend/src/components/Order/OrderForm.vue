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
        <v-list-item-subtitle>Кол-во ед. техники</v-list-item-subtitle>
        <v-list-item-title>
          <v-text-field
              type="number"
              v-model="order.vehicles_count"
              dense
              v-if="edit"
              :error="!!errors.vehicles_count"
              :error-count="1"
              :error-messages="errors.vehicles_count"
          />
          <span v-else>{{ order.vehicles_count }}</span>
        </v-list-item-title>
      </v-list-item>
      <v-list-item>
        <v-list-item-subtitle>Даты работ</v-list-item-subtitle>
        <v-list-item-title>
          <div v-if="edit" class="d-flex">
            <v-text-field
                placeholder="YYYY-MM-DD"
                v-mask="'####-##-##'"
                v-model="order.start_date"
                dense
                :error="!!errors.start_date"
                :error-count="1"
                :error-messages="errors.start_date"
            />
            <v-text-field
                placeholder="YYYY-MM-DD"
                class="ml-3"
                v-mask="'####-##-##'"
                v-model="order.finish_date"
                dense
                :error="!!errors.finish_date"
                :error-count="1"
                :error-messages="errors.finish_date"
            />
          </div>
          <span v-else>
            {{ moment(order.start_date).format('HH:mm DD.MM.YYYY') }} -
          {{ moment(order.finish_date).format('HH:mm DD.MM.YYYY') }}
          </span>
        </v-list-item-title>
      </v-list-item>
      <v-list-item>
        <v-list-item-content>
          <v-list-item-subtitle>Доп. параметры</v-list-item-subtitle>
          <FormAnswers
              v-if="order.form_answers && order.form_answers.length"
              v-model="order.form_answers"
              :edit="edit"
              :errors="errors"
          />
          <v-list-item-title v-else>
            <i>Данные не заполнены</i>
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script>
import FormAnswers from "@/components/Order/FormAnswers";
import moment from "moment";
import Swal from "sweetalert2-khonik";

export default {
  name: "OrderForm",
  props: ['value'],
  components: {FormAnswers},
  data() {
    return {
      moment: moment,
      edit: false,
      order: this.value,
      datesEdit: [this.value.start_date, this.value.finish_date],
      errors: {}
    }
  },
  watch: {
    datesEdit: {
      handler(v) {
        this.order.start_date = v[0];
        this.order.finish_date = v[1];
      }, deep: true
    }
  },
  methods: {
    save() {
      this.errors = {};
      this.$http.put(`orders/${this.order.id}`, this.order).then(() => {
        Swal.fire("Данные сохранены").then(() => {
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