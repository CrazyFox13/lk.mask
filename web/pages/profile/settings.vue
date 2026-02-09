<template>
  <div class="settings">
    <el-card>
      <div class="text-h3">Уведомления</div>
      <template v-for="(notificationType,k) in notificationTypes" :key="k">
        <div class="notification-item" v-if="isNotificationTypeVisible(notificationType)">
          <div class="text-h4">{{ notificationType.title }}</div>
          <p class="text-sub-subtitle">{{ notificationType.description }}</p>
          <div class="input-group" v-if="notificationType.key==='vehicle_orders' && !isCustomerCompany">
            <label>Получать заявки на технику</label>
            <VehicleTypeDialog v-model="subscribedVehicles"/>
          </div>
          <div class="input-group" v-if="notificationType.key==='vehicle_orders' && !isCustomerCompany">
            <label>Получать заявки из района</label>
            <RegionInputPicker v-model="subscribedCities"/>
          </div>
          <template v-if="isNotificationTypeVisible(notificationType)">
            <div class="notification-form" v-for="(uType,i) in notificationType.notification_type_user" :key="i">
              <p class="mb-0 mt-0">
                {{ typeMap[uType.way] }}
              </p>
              <el-switch
                  v-model="uType.active"
                  :active-value="1"
                  :inactive-value="0"
                  @change="setNotificationType(uType)"
              />
            </div>
          </template>
          <el-divider v-if="k === 0 && isNotificationTypeVisible(notificationType)"/>
        </div>
      </template>
      <el-checkbox
          v-model="user.silence"
          label='Режим "Не беспокоить"'
          class="mb-15"
          @change="setSilence()"
          :true-label="1"
          :false-label="0"
      />
      <div class="silence-form">
        <p class="mt-0 mb-0 text-subtitle">
          Не присылать с
        </p>
        <el-select v-model="fromTime" @change="setSilence()">
          <el-option
              v-for="(timeOption,i) in timeOptions"
              :key="i"
              :value="timeOption"
              :label="timeOption"
          />
        </el-select>
        <p class="mt-0 mb-0 text-subtitle">
          до
        </p>
        <el-select v-model="toTime" @change="setSilence()">
          <el-option
              v-for="(timeOption,i) in timeOptions"
              :key="i"
              :value="timeOption"
              :label="timeOption"
          />
        </el-select>
      </div>
    </el-card>
    <div class="del-block">
      <el-button link @click="destroy()">
        <SvgIcon name="trash" class="mr-2"/>
        Удалить аккаунт
      </el-button>
    </div>
  </div>
</template>

<script setup lang="ts">

import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, computed, useRouter, watch} from "#imports";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import VehicleTypeDialog from "~/components/Common/Forms/VehicleTypeDialog.vue";
import RegionInputPicker from "~/components/Common/Forms/RegionInputPicker.vue";
import moment from "moment";

const router = useRouter();
const {user} = storeToRefs(useAuthStore());

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

// Функция для проверки, должен ли элемент быть видимым
const isNotificationTypeVisible = (notificationType: any): boolean => {
  return !isCustomerCompany.value || (notificationType.key !== 'vehicle_orders' && notificationType.key !== 'filters');
};

const notificationTypes = ref<any[]>([]);

const subscribedVehicles = ref([]);
const subscribedCities = ref([]);

const data = await apiFetch(`notification-types`);
notificationTypes.value = data.notificationTypes;
subscribedVehicles.value = data.subscribedVehicles.map((v: any) => v.id);
subscribedCities.value = data.subscribedCities.map((c: any) => c.id);

const typeMap = {
  email: "E-mail",
  push: "Push-уведомления"
}

watch(subscribedVehicles, (veh) => {
  apiFetch(`subscribe-vehicles`, {
    method: "POST",
    body: {
      vehicle_types_id: veh
    }
  })
}, {deep: true})

watch(subscribedCities, (cities) => {
  apiFetch(`subscribe-cities`, {
    method: "POST",
    body: {
      geo_cities_id: cities
    }
  })
}, {deep: true})

const silenceTimeGetter = (hour: number, minute: number): string => {
  const offset = moment().utcOffset() / 60;
  let localHour = hour + offset;
  if (localHour > 23) {
    localHour = localHour - 24;
  } else if (localHour < 0) {
    localHour = 24 - localHour;
  }
  return `${localHour < 10 ? `0${localHour}` : localHour}:${minute < 10 ? `0${minute}` : minute}`
}

const silenceTimeSetter = (time: string): [number, number] => {
  const offset = moment().utcOffset() / 60;
  const parts = time.split(":").map(Number);
  let utcHour = parts[0] - offset;
  if (utcHour < 0) {
    utcHour = 24 + utcHour;
  } else if (utcHour > 23) {
    utcHour = utcHour - 24;
  }

  return [utcHour, parts[1]];
}

const fromTime = ref<string>(silenceTimeGetter(user!.value.silence_from, user!.value.silence_from_m));
const toTime = ref<string>(silenceTimeGetter(user!.value.silence_to, user!.value.silence_to_m));

const timeOptions = computed(() => {
  let list = [];
  for (let i = 0; i < 24; i++) {
    list.push(`${i < 10 ? `0${i}` : i}:00`)
    list.push(`${i < 10 ? `0${i}` : i}:30`)
  }
  return list;
});

const setNotificationType = (uType: any) => {
  apiFetch(`notification-types/${uType.notification_type_id}`, {
    method: "PUT",
    body: {
      way: uType.way,
      active: uType.active
    }
  })
}

const setSilence = () => {
  if (!user.value) return;
  apiFetch(`auth/set-silence`, {
    method: "POST",
    body: {
      silence: user.value.silence,
      silence_from: silenceTimeSetter(fromTime.value)[0],
      silence_from_m: silenceTimeSetter(fromTime.value)[1],
      silence_to: silenceTimeSetter(toTime.value)[0],
      silence_to_m: silenceTimeSetter(toTime.value)[1],
    }
  });
}

const destroy = () => {
  apiFetch(`auth/delete`, {
    method: "DELETE"
  }).then(() => {
    router.push(`/`)
  })
}


</script>

<style lang="scss">
.settings {
  display: flex;
  flex-flow: column;

  .el-card {
    order: 0;

    .text-h3 {
      margin-bottom: 30px;
    }

    .notification-item {
      margin-bottom: 30px;

      .text-h4 {
        margin-bottom: 12px;
      }

      .notification-form {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
    }
  }

  .del-block {
    order: 1;
    margin-top: 20px;
  }


  @media (min-width: 992px) {
    margin-top: -50px;
    .del-block {
      margin-top: 0;
      margin-bottom: 30px;
      text-align: right;
    }
    .el-card {
      order: 2;

      .text-h3 {
        margin-bottom: 40px;
      }

      .notification-item {
        margin-bottom: 40px;

        .text-h4 {
          margin-bottom: 12px;
        }

        .notification-form {
          max-width: 33%;
        }
      }
    }
  }

  .silence-form {
    display: flex;
    align-items: center;

    .el-select {
      margin-left: 5px;
      margin-right: 10px;
      width: 85px;

      .el-input {
        margin-bottom: 0;
      }

      &:last-child {
        margin-right: 0;
      }
    }
  }
}

.el-divider {
  margin-top: 40px;
  margin-bottom: 40px;
}
</style>