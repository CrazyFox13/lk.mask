<template>
  <div>
    <v-progress-circular indeterminate color="primary" v-if="!order"/>
    <v-row v-else>
      <v-col cols="12">
        <v-breadcrumbs :items="breadcrumps" divider="-"/>
        <v-alert color="error" class="white--text" v-if="order.deleted_at">
          Заявка была удалена
          {{ moment(order.deleted_at).format("HH:mm DD.MM.YYYY") }}
        </v-alert>
        <div class="text-h4">{{ order.title }}</div>
        <div class="text-caption" v-if="order.publish_date">
          Опубликовано {{ moment(order.publish_date).format("HH:mm DD.MM.YYYY") }}
        </div>

        <div class="d-flex align-center justify-space-between mt-2">
          <v-btn
              small
              :color="orderStatusLabelColor(order.moderation_status)"
              @click="moderationDialog=true"
          >
            {{ orderStatusLabelText(order.moderation_status) }}
          </v-btn>
          <div class="text-center">
            <v-btn @click="approve()" small :disabled="order.moderation_status!=='moderation'" color="success">
              Одобрить заявку
            </v-btn>
            <v-btn @click="cancel()" class="ml-sm-2 mt-3 mt-sm-0" small
                   :disabled="order.moderation_status!=='moderation'"
                   color="error">Отклонить заявку
            </v-btn>
          </div>
        </div>
      </v-col>
      <v-col cols="12" md="6">
        <OrderForm v-model="order"/>
        <OrderDetails class="mt-2" v-model="order"/>
        
        <!-- Причина отказа от заявки (когда заказчик сам отменил) -->
        <v-card v-if="order.moderation_status === 'canceled' && order.cancel_reason" class="mt-2">
          <v-card-title class="text-subtitle-1">
            <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
            Причина отказа от заявки
          </v-card-title>
          <v-card-text>
            <v-alert type="error" outlined dense>
              <strong>{{ cancelReasonMap[order.cancel_reason] || order.cancel_reason }}</strong>
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <OrderAmounts v-model="order"/>
        <v-card class="mt-2">
          <v-list>
            <v-list-item>
              <v-list-item-subtitle>Создано</v-list-item-subtitle>
              <v-list-item-title>{{ moment(order.created_at).format('HH:mm DD.MM.YYYY') }}</v-list-item-title>
            </v-list-item>
            <v-list-item v-if="order.publish_date">
              <v-list-item-subtitle>Опубликовано</v-list-item-subtitle>
              <v-list-item-title>{{ moment(order.publish_date).format('HH:mm DD.MM.YYYY') }}</v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Просмотров</v-list-item-subtitle>
              <v-list-item-title style="white-space: normal">{{ order.views_count }}</v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Создатель</v-list-item-subtitle>
              <v-list-item-title>
                <v-btn v-if="order.user" small outlined :to="`/users/${order.user_id}`">
                  {{ order.user.name }} {{ order.user.surname }}
                </v-btn>
                <i v-else>Пользователь удалён</i>
              </v-list-item-title>
            </v-list-item>
            <v-list-item>
              <v-list-item-subtitle>Компания</v-list-item-subtitle>
              <v-list-item-title>
                <v-btn v-if="order.company" small outlined :to="`/companies/${order.company_id}`">
                  {{ order.company.title }}
                </v-btn>
                <i v-else>Компания не найдена</i>
              </v-list-item-title>
            </v-list-item>

          </v-list>
        </v-card>
      </v-col>
      <v-col cols="12">
        <div class="text-subtitle-1">Адреса</div>
        <OrderAddresses v-if="order.addresses && order.addresses.length" :addresses="order.addresses"/>
      </v-col>
      <v-col cols="12">
        <div class="text-subtitle-1">Предложения</div>
        <OrderOffers :order_id="order.id"/>
      </v-col>
      <v-col cols="12">
        <div class="text-subtitle-1">Жалобы</div>
        <OrderClaims :order_id="order.id"/>
      </v-col>
      <v-col cols="12" v-if="order.user_id">
        <div class="text-subtitle-1">Другие заявки пользователя</div>
        <UserOrders :user_id="order.user_id"/>
      </v-col>
      <v-dialog v-model="moderationDialog" max-width="400">
        <OrderModeration v-model="order" @close="moderationDialog = false;"/>
      </v-dialog>
    </v-row>
  </div>
</template>

<script>
import Swal from "sweetalert2-khonik";
import moment from "moment";
import OrderAmounts from "@/components/Order/OrderAmounts";
import OrderModeration from "@/components/Order/OrderModeration";
import OrderAddresses from "@/components/Order/OrderAddresses";
import UserOrders from "@/components/User/UserOrders";
import OrderClaims from "@/components/Order/OrderClaims";
import OrderForm from "@/components/Order/OrderForm";
import OrderDetails from "@/components/Order/OrderDetails";
import OrderOffers from "@/components/Order/OrderOffers.vue";

export default {
  name: "OrderView",
  components: {
    OrderOffers,
    OrderDetails, OrderForm, OrderClaims, UserOrders, OrderAddresses, OrderModeration, OrderAmounts},
  data() {
    return {
      order_id: Number(this.$route.params.id),
      order: undefined,
      isLoading: false,
      errors: {},
      moment: moment,
      moderationDialog: false,
      cancelReasonMap: {
        'works_canceled': 'Отменились работы',
        'found_equipment': 'Нашли технику',
        'other': 'Другое'
      },
    }
  },
  created() {
    this.getOrder();
  },
  watch: {},
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Заявки',
          disabled: false,
          href: '/admin/orders',
        },
        {
          text: `${this.order?.title}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getOrder() {
      this.$http.get(`orders/${this.order_id}`).then(r => {
        this.order = r.body.order;
        this.moderationData = {
          status: this.order.moderation_status,
          text: this.order.moderation_message,
        }
      }).catch(() => {
        this.$router.replace('/orders')
      })
    },
    saveUser() {
      this.errors = {};
      this.$http.put(`orders/${this.order.id}`, this.order).then(r => {
        Object.keys(r.body.order).forEach(key => this.order[key] = r.body.order[key]);
        Swal.fire('Информация о заявке была обновлена')
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    approve() {
      Swal.fire({
        title: 'Вы уверены, что хотите одобрить заявку?',
        showDenyButton: false,
        showCancelButton: true,
        cancelButtonText: 'Закрыть',
        showCloseButton: false,
        showConfirmButton: true,
        confirmButtonText: `Одобрить заявку`,
        confirmButtonColor: '#4caf50'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          this.$http.post(`orders/${this.order.id}/approve`).then(() => {
            this.order.moderation_status = 'approved';
            this.order.moderation_message = null;
          })
        }
      })

    },
    cancel() {
      Swal.fire({
        title: 'Вы уверены, что хотите отклонить заявку?',
        showDenyButton: true,
        denyButtonText: `Отклонить заявку`,
        showCancelButton: true,
        cancelButtonText: 'Закрыть',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.post(`orders/${this.order.id}/cancel`).then(() => {
            this.order.moderation_status = 'canceled';
            this.order.moderation_message = null;
          })
        }
      })
    }
  }
}
</script>

<style scoped>

</style>