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
        <v-list-item-content>
          <v-list-item-subtitle>Стоимость</v-list-item-subtitle>
          <v-list>
            <v-list-item>
              <v-list-item-subtitle>Стоимость за</v-list-item-subtitle>
              <v-list-item-title>
                <v-select
                    v-if="edit"
                    :items="paymentUnits"
                    item-text="name"
                    item-value="id"
                    v-model="order.payment_unit_id"
                    dense
                />
                <span v-else>{{ order.payment_unit ? order.payment_unit.name : '-' }}</span>
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Стоимость</v-list-item-subtitle>
              <v-list-item-title>
                <v-text-field
                    dense
                    v-if="edit"
                    v-model="order.amount_account"
                />
                <span v-else>{{ order.amount_account ? order.amount_account : '-' }}</span>
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Стоимость c НДС</v-list-item-subtitle>
              <v-list-item-title>
                <v-text-field
                    dense
                    v-if="edit"
                    v-model="order.amount_account_vat"
                />
                <span v-else>{{ order.amount_account_vat ? order.amount_account_vat : '-' }}</span>
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Стоимость наличными</v-list-item-subtitle>
              <v-list-item-title>
                <v-text-field
                    dense
                    v-if="edit"
                    v-model="order.amount_cash"
                />
                <span v-else>{{ order.amount_cash ? order.amount_cash : '-' }}</span>
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Стоимость по договорённости</v-list-item-subtitle>
              <v-list-item-title>
                <v-checkbox v-model="order.amount_by_agreement" :disabled="!edit"/>
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script>
import Swal from "sweetalert2-khonik";

export default {
  name: "OrderAmounts",
  props: ['value'],
  data() {
    return {
      order: this.value,
      edit: false,
      errors: {},
      paymentUnits: []
    }
  },
  watch: {
    order: {
      handler() {
        this.$emit("input", this.order)
      }, deep: true,
    },
    edit() {
      if (this.edit && this.paymentUnits.length === 0) {
        this.getPaymentUnits();
      }
    }
  },
  methods: {
    getPaymentUnits() {
      this.$http.get('payment-units').then(({body}) => {
        this.paymentUnits = body.paymentUnits;
      })
    },
    save() {
      this.$http.post(`orders/${this.order.id}/set-budget`, this.order).then(() => {
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