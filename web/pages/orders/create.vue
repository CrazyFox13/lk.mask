<template>
  <div class="page order-creator">
    <div class="container">
      <h2 class="text-h2">Создание новой заявки</h2>
      <p class="text-gray">
        Вы можете создать заявку под любые ваши задачи. Постарайтесь максимально точно заполнить фому заявки.
        <br class="hidden-sm-and-down"/>
        Наши специалисты свяжутся с вами в ближайшее время для уточнения деталей и формирования заявки.
      </p>
      <el-row>
        <el-col :span="24" :md="16">
          <client-only>
            <el-card id="form">
              <div class="text-h3 mb-20">{{ title }}</div>

              <VehicleGroupPicker
                  v-model="vehicleGroupId"
                  class="input-group"
                  :groups="vehicleGroups"
                  @update:modelValue="startedChanging=true"
              />

              <VehicleTypePicker
                  v-model="vehicleTypeId"
                  class="input-group"
                  :types="selectedVehicleGroup.types"
                  :error="errors.vehicle_type_id"
                  @update:modelValue="startedChanging=true"
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
                  />
                  <el-switch
                      v-bind:class="{'ml-5':showLiving}"
                      v-if="showSecurity"
                      active-text="Охрана"
                      v-model="security"
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
              <div>
                <div class="flex gap-10 mt-20 mb-10">
                  <el-button :disabled="!formIsReady" :type="formIsReady?'primary':'info'" class="btn-submit"
                             @click="submit()">Отправить заявку
                  </el-button>
                  <el-button :disabled="!canSaveDraft" type="default" class="btn-submit"
                             @click="saveDraft()">Сохранить
                  </el-button>
                </div>
                <p class="text-subtitle text-gray">Нажимая на кнопку «Отправить заявку», вы принимаете правила сервиса</p>
              </div>
            </el-card>
          </client-only>
        </el-col>
        <el-col :span="24" :md="8">
          <CreateChatDisclaimer class="mb-20"/>
          <CreateStepsInfo/>
        </el-col>
      </el-row>
    </div>

    <AlarmDialog v-if="companyAlarmDialog" @close="companyAlarmDialog=false">
      <template #header>
        <div class="dialog-head">
          <el-button class="close-btn text-black hidden-md-and-up" circle text @click="companyAlarmDialog=false">
            <SvgIcon name="close"/>
          </el-button>
          <el-button class="close-btn text-black hidden-sm-and-down" circle text @click="companyAlarmDialog=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center">
          <div class="text-black modal-form-title m-0 text-center">Подтвердите ваши данные</div>
          <p>Чтобы разместить заявку, необходимо подтвердить "Персональные данные" в разделе Профиль</p>
          <el-link class="el-button el-button--primary profile-link" href="/profile">Перейти в профиль</el-link>
        </div>
      </template>
    </AlarmDialog>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, computed, useRouter, watch, generateTitle, useRoute, onMounted, nextTick, navigateTo, ElMessage} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
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
import {getCustomQuestions, getLivingQuestion, getSecurityQuestion} from "~/composables/orderEditorMixin";
import DocumentItem from "~/components/Common/DocumentItem.vue";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";
import CreateChatDisclaimer from "~/components/Orders/CreateChatDisclaimer.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

definePageMeta({
  middleware: ["auth"]
})

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;
const {user} = storeToRefs(useAuthStore());
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

const title = computed(() => {
  const type = selectedVehicleGroup.value.types && selectedVehicleGroup.value.types.find((t: any) => t.id === vehicleTypeId.value);
  return generateTitle(type, vehicleCount.value, customQuestions.value, formAnswers.value);
});

const route = useRoute()
const router = useRouter();

const companyAlarmDialog = ref(false);
const vehicleGroupId = ref(route.query.vehicle_group_id ? Number(route.query.vehicle_group_id) : undefined);
const vehicleTypeId = ref(route.query.vehicle_type_id ? Number(route.query.vehicle_type_id) : undefined);
const {vehicleGroups} = await apiFetch('vehicle-groups');
const formAnswers = ref<IFormAnswer[]>([]);
const vehicleCount = ref<number>(1);
const workDuration = ref<IWorkDuration>();
const living = ref(false);
const security = ref(false);
const addresses = ref<IAddress[]>([]);
const questions = ref<any[]>([]);
const budget = ref();
const description = ref("");
const documents = ref<any[]>([]);
const errors = ref<any>({});
const startedChanging = ref(false);
const {fetchFormQuestions} = useOrderStore();

const selectedVehicleGroup = computed(() => {
  const value = vehicleGroups.find((vg: any) => vg.id === vehicleGroupId.value);
  return value ? value : [];
});

const getForm = async () => {
  questions.value = await fetchFormQuestions(<number>vehicleGroupId.value, vehicleTypeId.value);
  formAnswers.value = questions.value.map((q: any) => {
    return {
      form_question_id: q.id,
      value: undefined
    };
  })
}

const customQuestions = computed(() => getCustomQuestions(questions.value));

const livingQuestion = computed(() => getLivingQuestion(questions.value));

const showLiving = computed(() => {
  return !!livingQuestion.value;
});

watch(living, (v) => {
  const a = formAnswers.value.find((a: any) => a.form_question_id === livingQuestion.value.id);
  if (a) {
    a.value = String(v);
  }
});

const securityQuestion = computed(() => getSecurityQuestion(questions.value));

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

const canSaveDraft = computed(() => {
  const body = orderRequest.value;
  return !!body.vehicle_type_id;
})

watch(vehicleTypeId, () => {
  if (typeof vehicleTypeId.value === 'undefined') questions.value = [];
  getForm();
});

watch(vehicleGroupId, () => {
  // Сбрасываем vehicleTypeId только если он не принадлежит выбранной группе
  if (vehicleTypeId.value && vehicleGroupId.value) {
    const selectedGroup = vehicleGroups.find((vg: any) => vg.id === vehicleGroupId.value);
    const typeExistsInGroup = selectedGroup?.types?.some((t: any) => t.id === vehicleTypeId.value);
    if (!typeExistsInGroup) {
      vehicleTypeId.value = undefined;
    }
  } else if (!vehicleGroupId.value) {
    vehicleTypeId.value = undefined;
  }
});

watch(startedChanging, () => {
  try {
    (window as any).ym(65983300, 'reachGoal', 'order_start')
  } catch (e) {
    console.log("todo: Yandex Metrika", 'creating order');
  }
})

// Загружаем форму при инициализации, если vehicleTypeId передан из query
onMounted(async () => {
  if (vehicleGroupId.value && vehicleTypeId.value) {
    await getForm();
  }
});

const fileUploaded = (file: any) => {
  documents.value.push(file);
}

const submit = async () => {
  errors.value = {};

  try {
    const response = await apiFetch(`orders/create`, {
      method: 'post',
      body: orderRequest.value,
    });
    
    console.log('Заявка успешно создана:', response);
    
    try {
      (window as any).ym(65983300, 'reachGoal', 'post_order')
    } catch (e) {
      console.log(e);
    }
    
    console.log('Перенаправление на /orders/created');
    await navigateTo('/orders/created');
  } catch (error: any) {
    console.error('Ошибка при создании заявки:', error);
    const status = error?.status;
    const body = error?.body;
    
    switch (status) {
      case 422:
        errors.value = body?.errors || {};
        break;
      case 403:
        companyAlarmDialog.value = true;
        break;
      default:
        console.error('Неизвестная ошибка:', status, body);
    }
  }
};

const saveDraft = async () => {
  errors.value = {};

  try {
    // Создаем запрос без параметра moderate, чтобы заявка сохранилась как черновик
    const draftRequest = {
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
    
    const response = await apiFetch(`orders/create`, {
      method: 'post',
      body: draftRequest,
    });
    
    console.log('Черновик успешно сохранен:', response);
    
    ElMessage.success('Заявка сохранена как черновик');
    
    // Перенаправляем на страницу редактирования заявки
    if (response && response.order && response.order.id) {
      await navigateTo(`/orders/${response.order.id}/edit`);
    } else {
      await navigateTo('/profile/orders');
    }
  } catch (error: any) {
    console.error('Ошибка при сохранении черновика:', error);
    const status = error?.status;
    const body = error?.body;
    
    switch (status) {
      case 422:
        errors.value = body?.errors || {};
        ElMessage.error('Ошибка валидации при сохранении черновика');
        break;
      case 403:
        companyAlarmDialog.value = true;
        break;
      default:
        console.error('Неизвестная ошибка:', status, body);
        ElMessage.error('Ошибка при сохранении черновика');
    }
  }
};
</script>

<style lang="scss">
.order-creator {

  .btn-submit {
    width: 100%;
    @media (min-width: 992px) {
      max-width: 247px;
    }
  }

  .flex {
    display: flex;
    flex-direction: column;
    gap: 10px;
    
    @media (min-width: 992px) {
      flex-direction: row;
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