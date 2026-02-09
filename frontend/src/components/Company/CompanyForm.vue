<template>
  <div>
    <v-select
        label="Тип"
        v-model="company.company_type_id"
        :items="companyTypes"
        item-text="title"
        item-value="id"
    />
    <v-text-field
        label="ИНН"
        v-model="company.inn"
        :error-messages="errors.inn"
        :error-count="1"
        :error="!!errors.inn"
    >
      <template #append-outer>
        <v-btn text @click="fetchDataByINN()">Заполнить</v-btn>
      </template>
    </v-text-field>
    <v-text-field
        label="Название"
        v-model="company.title"
        :error-messages="errors.title"
        :error-count="1"
        :error="!!errors.title"
    />
    <v-text-field
        label="Полное название"
        v-model="company.full_title"
        :error-messages="errors.full_title"
        :error-count="1"
        :error="!!errors.full_title"
    />
    <v-text-field
        label="ОГРН"
        v-model="company.ogrn"
        :error-messages="errors.ogrn"
        :error-count="1"
        :error="!!errors.ogrn"
    />
    <v-text-field
        label="КПП"
        v-model="company.kpp"
        :error-messages="errors.kpp"
        :error-count="1"
        :error="!!errors.kpp"
    />
    <v-text-field
        label="ОКПО"
        v-model="company.okpo"
        :error-messages="errors.okpo"
        :error-count="1"
        :error="!!errors.okpo"
    />
    <v-textarea
        label="Юр. адрес"
        v-model="company.legal_address"
        :error-messages="errors.legal_address"
        :error-count="1"
        :error="!!errors.legal_address"
    />
    <v-textarea
        label="Факт. адрес"
        v-model="company.address"
        :error-messages="errors.address"
        :error-count="1"
        :error="!!errors.address"
    />
    <v-text-field
        label="Имя директора"
        v-model="company.director"
        :error-messages="errors.director"
        :error-count="1"
        :error="!!errors.director"
    />
    <v-text-field
        label="E-mail"
        v-model="company.email"
        :error-messages="errors.email"
        :error-count="1"
        :error="!!errors.email"
    />
    <v-text-field
        label="Телефон"
        v-model="company.phone"
        :error-messages="errors.phone"
        :error-count="1"
        :error="!!errors.phone"
    />

    <v-text-field
        label="Сайт"
        v-model="company.website"
        :error-messages="errors.website"
        :error-count="1"
        :error="!!errors.website"
    />

    <v-textarea
        label="Описание"
        v-model="company.description"
        :error-messages="errors.description"
        :error-count="1"
        :error="!!errors.description"
    />

    <datepicker
        v-model="company.legal_registration_date"
        label="Дата регистрации юр. лица"
    />

    <datepicker
        v-model="company.membership_fee_paid_at"
        label="Дата оплаты членского взноса"
    />

    <v-text-field
        label="Базовый номер компании"
        v-model="company.reg_number"
        :error-messages="errors.reg_number"
        :error-count="1"
        :error="!!errors.reg_number"
    />

    <v-switch v-model="company.self_park"
              label="Собственный парк"/>

    <v-switch v-model="company.instant_moderation"
              label="Мгновенная модерация"/>

    <v-switch v-model="company.verified"
              label="Верификация включена"/>

    <v-btn color="primary" @click="saveCompany()">Сохранить</v-btn>
  </div>
</template>

<script>
import Swal from "sweetalert2-khonik";
import Datepicker from "@/components/Common/Datepicker";

export default {
  name: "CompanyForm",
  components: {Datepicker},
  props: ['value'],
  data() {
    return {
      errors: {},
      companyTypes: [],
      company: this.value,
    }
  },
  created() {
    this.getCompanyTypes();
  },
  watch: {
    value: {
      handler() {
        this.company = {...this.company, ...this.value}
      }, deep: true
    }
  },
  methods: {
    fetchDataByINN() {
      this.$http.get(`company-by-inn?inn=${this.company.inn}`).then(({body}) => {
        if (body.data.length > 0) {
          const suggestion = body.data[0].data;
          if (suggestion) {
            const data = {
              title: suggestion.name?.full,
              full_title: suggestion.name?.full_with_opf,
              ogrn: suggestion.ogrn,
              kpp: suggestion.kpp,
              okpo: suggestion.okpo,
              legal_address: suggestion.address.value,
              address: suggestion.address.value,
              director: suggestion.management?.name
            }
            this.$set(this, 'company', {...this.company, ...data})
          }
        }
      })
    },
    getCompanyTypes() {
      this.$http.get(`company-types`).then(r => {
        this.companyTypes = r.body.companyTypes;
      })
    },
    saveCompany() {
      this.errors = {};
      if (this.company.id) {
        this.update()
      } else {
        this.store();
      }
    },
    update() {
      this.$http.put(`companies/${this.company.id}`, this.company).then(r => {
        Object.keys(r.body.company).forEach(key => {
          this.company[key] = r.body.company[key]
        });
        this.$emit('input', this.company);
        Swal.fire('Информация о компании была обновлена')
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    store() {
      this.$http.post(`companies`, {
        user_id: this.$route.query.user_id,
        ...this.company,
      }).then(r => {
        this.$router.push(`/companies/${r.body.company.id}`)
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>