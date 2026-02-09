<template>
  <div>
    <v-progress-circular
        indeterminate
        color="primary"
        v-if="!user"
    />
    <v-row v-else>
      <v-breadcrumbs
          :items="breadcrumps"
          divider="-"
      />
      <v-col cols="12">
        <v-alert type="error" v-if="user.deleted_at" dense>Пользователь удалён</v-alert>
        <div class="d-flex align-center">
          <v-img max-width="75" class="rounded-circle"
                 :src="user.avatar?user.avatar:require('@/assets/images/avatar.png')" width="75"
                 height="75"/>
          <v-rating
              v-model="user.rating"
              :length="5"
              readonly
              half-increments
          />
        </div>
      </v-col>
      <v-col cols="12" md="6">
        <div class="text-subtitle-1">Профиль</div>
        <v-text-field
            label="Фамиля"
            v-model="user.surname"
            :error-messages="errors.surname"
            :error-count="1"
            :error="!!errors.surname"
        />
        <v-text-field
            label="Имя"
            v-model="user.name"
            :error-messages="errors.name"
            :error-count="1"
            :error="!!errors.name"
        />
        <v-text-field
            label="E-mail"
            v-model="user.email"
            :error-messages="errors.email"
            :error-count="1"
            :error="!!errors.email"
        >
          <template #append-outer>
            <v-btn
                class="mr-2"
                color="primary"
                x-small
                @click="sendEmailConfirmation()"
            >Отправить ссылку
            </v-btn>
            <v-simple-checkbox
                :value="!!user.email_verified_at"
                hide-details
                dense
                @click="toggleEmailVerification()"
            />
          </template>
        </v-text-field>
        <v-text-field
            label="Телефон"
            v-model="user.phone"
            :error-messages="errors.phone"
            :error-count="1"
            :error="!!errors.phone"
        >
          <template #append-outer>
            <v-simple-checkbox
                :value="!!user.phone_verified_at"
                hide-details
                dense
                @click="togglePhoneVerification()"
            />
          </template>
        </v-text-field>
        <!--<v-autocomplete
            label="Регион"
            v-model="user.region_id"
            :items="regions"
            :loading="isLoadingRegions"
            :search-input.sync="searchRegion"
            item-value="id"
            item-text="title"
            @keydown.enter="createRegion()"
        />-->
        <v-autocomplete
            label="Город"
            v-model="user.geo_city_id"
            :items="cities"
            :loading="isLoadingCities"
            :search-input.sync="searchCity"
            item-value="id"
            item-text="name"
        />
        <v-btn color="primary" @click="saveUser()">Изменить профиль</v-btn>
      </v-col>

      <v-col cols="12" md="6">
        <div class="text-subtitle-1">Компания</div>
        <v-list v-if="user.company">
          <v-list-item :key="field.key" v-for="field in companyFields">
            <v-list-item-subtitle>{{ field.label }}</v-list-item-subtitle>
            <v-list-item-title>{{ user.company[field.key] }}</v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-subtitle>Права</v-list-item-subtitle>
            <v-list-item-title>{{ user.company_role === 'boss' ? 'АК' : 'СК' }}</v-list-item-title>
          </v-list-item>

          <v-list-item>
            <v-list-item-action>
              <v-btn small color="info" :to="`/companies/${user.company_id}`">Открыть компанию</v-btn>
            </v-list-item-action>
          </v-list-item>
        </v-list>
        <v-alert type="info" outlined v-else>
          Нет компании

          <v-btn class="ml-3" color="info" small :to="`/companies/create?user_id=${user.id}`">Создать</v-btn>
        </v-alert>
      </v-col>
      <v-col cols="12">
        <v-textarea
            label="Комментарий"
            v-model="user.comment"
            :error-messages="errors.name"
            :error-count="1"
            :error="!!errors.name"
        />
        <v-btn color="primary" @click="setComment()">Сохранить</v-btn>
      </v-col>
      <v-col cols="12">
        <div class="text-subtitle-1">Заявки пользователя</div>
        <UserOrders :user_id="user_id"/>
      </v-col>

      <v-col cols="12">
        <div class="text-subtitle-1">Поиски пользователя</div>
        <UserOrderFilters :user_id="user_id"/>
      </v-col>
      <v-col cols="12">
        <UserSubscribedVehicles :subscribed="user.subscribed_vehicles" :user="user"/>
      </v-col>
      <v-col cols="12">
        <UserSubscribedCities :subscribed="user.subscribed_cities" :user="user"/>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Swal from "sweetalert2-khonik";
import UserOrders from "@/components/User/UserOrders";
import moment from "moment";
import UserOrderFilters from "@/components/User/UserOrderFilters";
import UserSubscribedVehicles from "@/components/User/UserSubscribedVehicles";
import UserSubscribedCities from "@/components/User/UserSubscribedCities";

export default {
  name: "UserView",
  components: {UserSubscribedCities, UserSubscribedVehicles, UserOrderFilters, UserOrders},
  data() {
    return {
      user_id: Number(this.$route.params.id),
      user: undefined,
      companyFields: [
        {key: 'title', label: 'Название'},
        {key: 'director', label: 'Директор'},
        {key: 'phone', label: 'Телефон'},
        {key: 'email', label: 'E-mail'},
        {key: 'website', label: 'Сайт'},
      ],
      errors: {},
      isLoadingCities: false,
      cities: [],
      searchCity: '',
      /*isLoadingRegions: false,
      regions: [],
      searchRegion: '',*/
    }
  },
  created() {
    this.getUser();
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Пользователи',
          disabled: false,
          href: '/admin/users',
        },
        {
          text: `${this.user?.name} ${this.user?.surname}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  watch: {
    searchCity() {
      this.getCities();
    },
    /*searchRegion() {
      this.getRegions();
    },
    'user.region_id': {
      handler() {
        this.getCities();
      }
    }*/
  },
  methods: {
    toggleEmailVerification() {
      this.user.email_verified_at = this.user.email_verified_at ? null : moment().toDate();
    },
    togglePhoneVerification() {
      this.user.phone_verified_at = this.user.phone_verified_at ? null : moment().toDate();
    },
    getUser() {
      this.$http.get(`users/${this.user_id}`).then(r => {
        this.user = r.body.user;
        this.getCities();
      }).catch(() => {
        this.$router.replace('/users')
      })
    },
    saveUser() {
      this.errors = {};
      this.$http.put(`users/${this.user.id}`, this.user).then(r => {
        Object.keys(r.body.user).forEach(key => this.user[key] = r.body.user[key]);
        Swal.fire('Информация о пользователе была обновлена')
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    setComment() {
      this.errors = {};
      this.$http.post(`users/${this.user.id}/comment`, {
        comment: this.user.comment
      }).then(() => {
        Swal.fire('Сохранено')
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    getCities() {
      if (this.isLoadingCities) return;
      this.isLoadingCities = true;
      this.$http.get(`geo-cities?sort_by_field=${this.user.geo_city_id ? this.user.geo_city_id : ''}&search=${this.searchCity ? this.searchCity : ''}`).then(r => {
        this.cities = r.body.cities;
        this.isLoadingCities = false;
      })
    },
    sendEmailConfirmation() {
      this.$http.post(`users/${this.user.id}/send-email-confirmation`, {
        email: this.user.email,
      }).then(({body}) => {
        Swal.fire({
          icon: 'success',
          text: `Письмо с подтверждением было отправлено на адрес ${body.user.email}`
        })
      }).catch(({body}) => {
        Swal.fire({
          icon: 'error',
          text: body.message
        })
      })
    }
  }
}
</script>

<style scoped>

</style>