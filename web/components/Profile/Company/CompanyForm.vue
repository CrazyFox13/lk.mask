<template>
  <el-card>
    <div class="text-h3">Персональные данные компании</div>
    <div v-if="company && company.moderation_status==='canceled'" class="moderation-failed mt-30 mb-30">
      <div class="flex align-items-center">
        <SvgIcon name="alert-red" class="mr-2"/>
        <div class="text-h5">Компания отклонена модератором</div>
      </div>
      <p>{{ company.moderation_message }}</p>
    </div>
    <div class="input-group">
      <label class="input-title">ИНН</label>
      <div>
        <TextInput
            placeholder="Введите ИНН"
            v-model="company.inn"
            :error="errors.inn"
            @focusout="validateInn()"
            :read_only="companyApproved"
        />
        <el-button
            v-if="!companyApproved"
            :type="innReady?'primary':'info'"
            :read_only="!innReady"
            class="inn-btn"
            @click="getCompanyByInn()"
        >Заполнить по ИНН
        </el-button>
      </div>
    </div>

    <div class="input-group">
      <label class="input-title">Название компании</label>
      <TextInput
          placeholder="Введите название"
          v-model="company.title"
          :error="errors.title"
          :read_only="companyApproved"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Полное наименование</label>
      <TextInput
          placeholder="Введите полное наименование"
          v-model="company.full_title"
          :error="errors.full_title"
          :read_only="companyApproved"
      />
    </div>
    <div class="input-group">
      <label class="input-title">ОГРН</label>
      <TextInput
          placeholder="Введите ОГРН"
          v-model="company.ogrn"
          :error="errors.ogrn"
          :read_only="companyApproved"
      />
    </div>
    <div class="input-group">
      <label class="input-title">КПП</label>
      <TextInput
          placeholder="Введите КПП"
          v-model="company.kpp"
          :error="errors.kpp"
          :read_only="companyApproved"
      />
    </div>

    <div class="input-group">
      <label class="input-title">ОКПО</label>
      <TextInput
          placeholder="Введите ОКПО"
          v-model="company.okpo"
          :error="errors.okpo"
          :read_only="companyApproved"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Генеральный директор</label>
      <TextInput
          placeholder="Введите ФИО"
          v-model="company.director"
          :error="errors.director"
          :read_only="companyApproved"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Юридический адрес</label>
      <TextInput
          placeholder="Введите адрес"
          v-model="company.legal_address"
          :error="errors.legal_address"
          :read_only="companyApproved"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Фактический адрес</label>
      <TextInput
          placeholder="Введите адрес"
          v-model="company.address"
          :error="errors.address"
          :read_only="companyApproved"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Телефон</label>
      <TextInput
          mask="+7 (###) ###-##-##"
          placeholder="+7 (999)-999-99-99"
          v-model="company.phone"
          :error="errors.phone"
      />
    </div>

    <div class="input-group">
      <label class="input-title">E-mail</label>
      <TextInput
          placeholder="mail@yahoo.ru"
          v-model="company.email"
          :error="errors.email"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Cайт компании</label>
      <TextInput
          placeholder="Ссылка на сайт"
          v-model="company.website"
          :error="errors.website"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Описание деятельности компании</label>
      <TextInput
          type="textarea"
          placeholder="Введите подробное описание"
          v-model="company.description"
          :error="errors.description"
      />
    </div>

    <div class="input-group">
      <label class="input-title">Использую технику</label>
      <VehicleTypePicker
          v-model="company.vehicle_types_id"
          :error="errors.vehicle_types_id"
          :collapsed="true"
      />
    </div>
    <p class="text-subtitle text-error" v-if="errors.vehicle_types_id">{{ errors.vehicle_types_id }}</p>

    <div class="action-btns mt-20">
      <el-button type="primary" @click="save()">Сохранить данные</el-button>
      <el-button type="primary" plain v-if="company && company.moderation_status==='draft'" @click="moderate()">
        Отправить на модерацию
      </el-button>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import TextInput from "~/components/Common/Forms/TextInput.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {type ICompany} from "~/stores/user";
import {apiFetch, computed, ref} from "#imports";
import VehicleTypePicker from "~/components/Common/Forms/VehicleTypePicker.vue";
import SvgIcon from "~/components/SvgIcon.vue";

const {user} = storeToRefs(useAuthStore());
const emit = defineEmits(['onModeration', 'innExists']);
const company = ref<ICompany>(user.value && user.value.company ? user.value.company : {
  id: undefined,
  company_type_id: undefined,
  inn: '',
  title: '',
  full_title: '',
  ogrn: '',
  kpp: '',
  okpo: '',
  legal_address: '',
  address: '',
  director: '',
  phone: '',
  email: '',
  website: '',
  description: '',
  rating: undefined,
  reg_number: '',
  documents: [],
})
const errors = ref<any>({});

const innReady = computed(() => {
  return company.value.inn && company.value.inn.length >= 10;
});

const companyApproved = computed(() => {
  return company.value.moderation_status === 'approved';
})

const getCompanyByInn = async () => {
  const {data} = await apiFetch(`company-by-inn?inn=${company.value.inn}`);
  if (data.length > 0) {
    const suggestion = data[0].data;
    if (suggestion) {
      company.value.title = suggestion.name?.full
      company.value.full_title = suggestion.name?.full_with_opf
      company.value.ogrn = suggestion.ogrn
      company.value.kpp = suggestion.okpo
      company.value.okpo = suggestion.okpo
      company.value.legal_address = suggestion.address.value
      company.value.address = suggestion.address.value
      company.value.director = suggestion.management?.name
    }
  }
}

const save = () => {
  errors.value = {}
  if (company.value.id) {
    update();
  } else {
    store();
  }
}

const store = () => {
  apiFetch(`companies`, {
    method: "POST",
    body: company.value
  }).then((body) => {
    company.value = {
      ...company.value,
      ...body.company
    }
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}

const update = () => {
  apiFetch(`companies/${company.value.id}`, {
    method: "PUT",
    body: company.value
  }).then((body) => {
    company.value = {
      ...company.value,
      ...body.company
    }
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}

const moderate = () => {
  errors.value = {}
  apiFetch(`companies/${company.value.id}/moderate`, {
    method: "POST",
  }).then((body) => {
    emit('onModeration');
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}

const validateInn = () => {
  apiFetch(`validate-inn?inn=${company.value.inn}`).then(({company_exists, company_id}) => {
    if (company_exists) {
      if (company.value.id !== company_id) {
        emit('innExists');
      }
    }
  });
}
</script>

<style scoped lang="scss">
.text-h3 {
  margin-bottom: 20px;
  @media (min-width: 992px) {
    margin-bottom: 30px;
  }
}

.inn-btn {
  margin-top: 10px;
  @media (min-width: 992px) {
    margin-top: 13px;
  }
}

.action-btns {
  display: flex;
  flex-flow: column;
  row-gap: 10px;

  .el-button {
    margin-left: 0 !important;
  }

  @media (min-width: 992px) {
    flex-flow: row;
    row-gap: 0;;
    column-gap: 20px;
  }
}

.moderation-failed {
  border: 1px solid #F92609;
  border-radius: 10px;
  padding: 20px;

  .text-h5, p {
    margin: 0;
  }

  p {
    margin-top: 15px;
  }
}
</style>