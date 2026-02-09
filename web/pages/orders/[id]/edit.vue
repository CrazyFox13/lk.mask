<template>
  <div class="page order-creator">
    <div class="container">
      <h2 class="text-h2">Редактирование заявки</h2>
      <el-row>
        <el-col :span="24" :md="16">
          <client-only>
            <el-card>
              <div class="text-h3 mb-20">{{ title }}</div>

              <VehicleGroupPicker
                  v-model="vehicleGroupId"
                  class="input-group"
                  :groups="vehicleGroups"
              />

              <VehicleTypePicker
                  v-model="vehicleTypeId"
                  class="input-group"
                  :types="selectedVehicleGroup.types"
                  :error="errors.vehicle_type_id"
              />

              <CustomQuestion
                  v-for="(question,i) in customQuestions"
                  :key="question.id"
                  class="input-group"
                  :question="question"
                  v-model="formAnswers[i]"
                  :errors="errors"
                  :index="i"
              />

              <p class="text-error text-hint custom-form-error" v-if="!!errors.form_answers">
                {{ errors.form_answers }}
              </p>

              <VehicleCountPicker
                  class="input-group"
                  v-model="vehicleCount"
                  :error="errors.vehicles_count"
              />

              <div class="input-group" v-if="showLiving || showSecurity">
                <label class="input-title">Дополнительные параметры</label>
                <div>
                  <el-switch
                      v-if="showLiving"
                      active-text="Проживание"
                      v-model="living"
                      :inactive-value="'false'"
                      :active-value="'true'"
                  />
                  <el-switch
                      v-bind:class="{'ml-5':showLiving}"
                      v-if="showSecurity"
                      active-text="Охрана"
                      v-model="security"
                      :inactive-value="'false'"
                      :active-value="'true'"
                  />
                </div>
              </div>

              <WorkDurationPicker
                  class="input-group"
                  v-model="workDuration"
                  :errors="errors"
              />

              <el-divider/>
              <div class="text-h4 mb-20">Адрес</div>
              <AddressesForm v-model="addresses" :errors="errors" :vehicle-group-id="vehicleGroupId"/>

              <el-divider v-if="!isCustomerCompany"/>
              <div v-if="!isCustomerCompany" class="text-h4">Бюджет</div>
              <BudgetForm v-if="!isCustomerCompany" :vehicle-type-id="vehicleTypeId" v-model="budget" :errors="errors"/>

              <el-divider/>
              <div class="text-h4">Описание</div>
              <p>
                Подробно опишите детали вашей заявки (минимум 15 символов)
              </p>
              <div class="input-group">
                <div v-bind:class="{'input-error':!!errors.description}" style="width: 100%">
                  <el-input
                      autosize
                      type="textarea"
                      placeholder="Например: Копка траншеи под водопровод, длина около 300 м, глубина 1,7. На объекте пропускной режим, есть проживание"
                      v-model="description"
                      min="15"
                  />
                  <p v-if="!!errors.description">{{ errors.description }}</p>
                </div>
              </div>

              <el-divider/>
              <div class="text-h4">Материалы к заявке</div>
              <p>
                Вы можете добавить фотографии и файлы к заявке, которые будут видны всем пользователям
              </p>
              <div class="flex flex-wrap">
                <DocumentItem
                    v-for="(document,i) in documents"
                    :key="i"
                    :document="document"
                    :edit="true"
                    @deleted="documents.splice(i,1)"
                />
              </div>
              <FileUploader
                  :multiple="true"
                  @file_data="fileUploaded"
              />
              <div class="form-actions">
                <!-- Кнопки для черновика -->
                <template v-if="isDraft">
                  <el-button 
                    :disabled="!formIsReady" 
                    :type="formIsReady?'primary':'info'" 
                    class="btn-submit"
                    @click="submitForModeration()">
                    Отправить
                  </el-button>
                  <el-button 
                    :disabled="!canSaveDraft" 
                    :type="canSaveDraft?'primary':'info'" 
                    plain
                    class="btn-submit"
                    @click="saveDraft()">
                    Сохранить
                  </el-button>
                  <el-button 
                    type="primary" 
                    plain 
                    class="btn-submit" 
                    @click="cancel()">
                    Отменить
                  </el-button>
                </template>
                <!-- Кнопки для других статусов -->
                <template v-else>
                  <el-button 
                    :disabled="!formIsReady" 
                    :type="formIsReady?'primary':'info'" 
                    class="btn-submit"
                    @click="submit()">
                    Сохранить
                  </el-button>
                  <el-button 
                    type="primary" 
                    plain 
                    class="btn-submit" 
                    @click="back()">
                    Отменить
                  </el-button>
                </template>
              </div>
            </el-card>
          </client-only>
        </el-col>
        <el-col :span="24" :md="8">
          <CreateStepsInfo/>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  apiFetch,
  computed,
  useRoute,
  useRouter,
  watch,
  generateTitle,
  ElMessage
} from "#imports";

import VehicleGroupPicker from "~/components/Orders/OrderForm/VehicleGroupPicker.vue";
import VehicleTypePicker from "~/components/Orders/OrderForm/VehicleTypePicker.vue";
import CustomQuestion from "~/components/Orders/OrderForm/CustomQuestion.vue";
import type {IFormAnswer} from "~/types/formAnswer";
import VehicleCountPicker from "~/components/Orders/OrderForm/VehicleCountPicker.vue";
import WorkDurationPicker from "~/components/Orders/OrderForm/WorkDurationPicker.vue";
import type {IWorkDuration} from "~/types/workDuration";
import AddressesForm from "~/components/Orders/OrderForm/AddressesForm.vue";
import type {IAddress} from "~/types/address";
import BudgetForm from "~/components/Orders/OrderForm/BudgetForm.vue";
import FileUploader from "~/components/Common/Forms/FileUploader.vue";
import CreateStepsInfo from "~/components/Orders/CreateStepsInfo.vue";
import {useOrderStore} from "~/stores/order";
import {
  getCustomQuestions,
  getLivingAnswer,
  getLivingQuestion, getSecurityAnswer,
  getSecurityQuestion
} from "~/composables/orderEditorMixin";
import DocumentItem from "~/components/Common/DocumentItem.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

definePageMeta({
  middleware: ["auth"]
});

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;
const {user} = storeToRefs(useAuthStore());
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

const {fetchFormQuestions} = useOrderStore();
const route = useRoute();
const orderId = Number(route.params.id);
const data = await apiFetch(`orders/${orderId}`);
const order = ref(data.order);

const title = computed(() => {
  const type = selectedVehicleGroup.value.types && selectedVehicleGroup.value.types.find((t: any) => t.id === vehicleTypeId.value);
  return generateTitle(type, vehicleCount.value, customQuestions.value, formAnswers.value);
});

const vehicleGroupId = ref(order.value?.vehicle_type?.group?.id);
const vehicleTypeId = ref(order.value?.vehicle_type_id);
const {vehicleGroups} = await apiFetch('vehicle-groups');
const formAnswers = ref<IFormAnswer[]>(order.value.form_answers);
const vehicleCount = ref<number>(order.value.vehicles_count);
const workDuration = ref<IWorkDuration>({
  start_date: order.value.start_date,
  finish_date: order.value.finish_date
});
const questions = ref<any[]>([]);

const getForm = async () => {
  questions.value = await fetchFormQuestions(vehicleGroupId.value, vehicleTypeId.value);
}

if (vehicleTypeId.value) await getForm()

const livingQuestion = computed(() => getLivingQuestion(questions.value));
const securityQuestion = computed(() => getSecurityQuestion(questions.value));
const living = ref(getLivingAnswer(formAnswers.value, questions.value));
const security = ref(getSecurityAnswer(formAnswers.value, questions.value));
const addresses = ref<IAddress[]>(order.value.addresses);

const budget = ref({
  payment_unit_id: order.value.payment_unit_id,
  amount_account_vat: order.value.amount_account_vat,
  amount_account: order.value.amount_account,
  amount_cash: order.value.amount_cash,
  amount_by_agreement: order.value.amount_by_agreement,
  no_haggling: order.value.no_haggling,
});

const description = ref(order.value.description);
const documents = ref<any[]>(order.value.documents);
const errors = ref<any>({});

const selectedVehicleGroup = computed(() => {
  const value = vehicleGroups.find((vg: any) => vg.id === vehicleGroupId.value);
  return value ? value : [];
});

const customQuestions = computed(() => getCustomQuestions(questions.value));

const showLiving = computed(() => {
  return !!livingQuestion.value;
});

watch(living, (v) => {
  const a = formAnswers.value.find((a: any) => a.form_question_id === livingQuestion.value.id);
  if (a) {
    a.value = String(v);
  }
});

const showSecurity = computed(() => {
  return !!securityQuestion.value;
});

watch(security, (v) => {
  const a = formAnswers.value.find((a: any) => a.form_question_id === securityQuestion.value.id);
  if (a) {
    a.value = String(v);
  }
});

const orderRequest = computed(() => {
  return {
    vehicle_type_id: vehicleTypeId.value,
    vehicles_count: vehicleCount.value,
    form_answers: formAnswers.value.filter((a: any) => a.value !== undefined),
    addresses: addresses.value,
    ...budget.value,
    ...workDuration.value,
    description: description.value,
    documents: [...documents.value],
    moderate: 1,
    communication_way: 'call'
  };
});

const formIsReady = computed(() => {
  const body = orderRequest.value;
  return body.vehicle_type_id && body.addresses && body.start_date && body.finish_date && body.description && body.description.length >= 15;
})

const isDraft = computed(() => {
  return order.value?.moderation_status === 'draft';
})

const canSaveDraft = computed(() => {
  const body = orderRequest.value;
  return !!body.vehicle_type_id;
})

watch(vehicleTypeId, () => {
  if (typeof vehicleTypeId.value === 'undefined') questions.value = [];
  getForm();
});

watch(vehicleGroupId, () => {
  vehicleTypeId.value = undefined;
});

const fileUploaded = (file: any) => {
  documents.value.push(file);
}

const router = useRouter();

// Отправка на модерацию (публикация заявки)
const submitForModeration = () => {
  errors.value = {};

  const requestBody = {
    ...orderRequest.value,
    moderate: 1
  };

  apiFetch(`orders/${orderId}`, {
    method: 'put',
    body: requestBody,
  }).then(() => {
    router.push(`/orders/${orderId}/edited`);
  }).catch(({status, body}) => {
    switch (status) {
      case 422:
        errors.value = body.errors;
        break;
    }
  })
};

// Сохранение черновика без публикации
const saveDraft = () => {
  errors.value = {};

  const requestBody = {
    vehicle_type_id: vehicleTypeId.value,
    vehicles_count: vehicleCount.value,
    form_answers: formAnswers.value.filter((a: any) => a.value !== undefined),
    addresses: addresses.value,
    ...budget.value,
    ...workDuration.value,
    description: description.value,
    documents: [...documents.value],
    communication_way: 'call'
    // НЕ передаем moderate, чтобы заявка осталась черновиком
  };

  apiFetch(`orders/${orderId}`, {
    method: 'put',
    body: requestBody,
  }).then(() => {
    ElMessage.success('Изменения сохранены');
    router.push(`/orders/${orderId}`);
  }).catch(({status, body}) => {
    switch (status) {
      case 422:
        errors.value = body.errors;
        break;
    }
  })
};

// Сохранение изменений (для не-черновиков)
const submit = () => {
  errors.value = {};

  apiFetch(`orders/${orderId}`, {
    method: 'put',
    body: orderRequest.value,
  }).then(() => {
    router.push(`/orders/${orderId}/edited`);
  }).catch(({status, body}) => {
    switch (status) {
      case 422:
        errors.value = body.errors;
        break;
    }
  })
};

// Отмена изменений и возврат назад
const cancel = () => {
  router.back();
}

const back = () => {
  router.back();
}
</script>

<style lang="scss">
.order-creator {

  .form-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;

    @media (min-width: 992px) {
      flex-direction: row;
      align-items: center;
      gap: 20px;
    }
  }

  .btn-submit {
    width: 100%;
    margin: 0;

    @media (min-width: 992px) {
      max-width: 247px;
      width: auto;
    }
  }

  .el-col:last-of-type {
    margin-top: 20px;
    @media (min-width: 992px) {
      margin-top: 0;
      padding-left: 20px;
    }
  }

  .yandex-container {
    height: 185px;
    @media (min-width: 992px) {
      height: 242px;
    }
  }

  .profile-link {
    width: 100%;
    @media (min-width: 992px) {
      max-width: 200px;
    }
  }

  .custom-form-error {
    @media (min-width: 992px) {
      padding-left: 40%;
    }
  }
}
</style>