<template>
  <div v-bind:class="{'hidden-sm-and-down':geoCityConfirmed}">
    <div class="container flex justify-between" >
      <div class="geo-city">
        <el-button link v-if="geoCity" @click="openCityDialog()">
          <SvgIcon name="send"/>
          {{ cityTitle }}
        </el-button>
        <el-button
            v-if="geoCity && !geoCityConfirmed"
            size="small"
            type="primary"
            @click="confirmCity()"
        >Да
        </el-button>
        <el-button
            v-if="geoCity && !geoCityConfirmed"
            size="small"
            link
            @click="openCityDialog()"
        >Изменить
        </el-button>
      </div>
      <div class="flex hidden-sm-and-down">
        <nuxt-link class="el-link" to="/download-app">
          Приложение
        </nuxt-link>
        <nuxt-link v-if="!isCustomerCompany && user" class="el-link bar-link" to="/favorites/orders">
          <SvgIcon name="star-outline" class="mr-1"/>
          Избранное
        </nuxt-link>
        <nuxt-link class="el-link bar-link" to="/profile/notifications">
          <SvgIcon name="bell" class="mr-1">
            <template #badge>
              <BadgeLabel :value="badges.total_badges_value"/>
            </template>
          </SvgIcon>
          Уведомления
        </nuxt-link>
        <nuxt-link class="el-link bar-link" to="/profile" v-if="user">
          <SvgIcon name="user" class="mr-1"/>
          {{user.name}} {{user.surname}}
        </nuxt-link>
        <nuxt-link class="el-link bar-link" to="/auth/sign-in" v-else>
          <SvgIcon name="user" class="mr-1"/>
          Войти
        </nuxt-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "../SvgIcon.vue";
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";
import {computed} from "#imports";
import {emitter} from "~/composables/emitter";
import {useBadgeStore} from "~/stores/badges";
import BadgeLabel from "~/components/Common/BadgeLabel.vue";


const {confirmCity} = useAuthStore();
const {geoCity, geoCityConfirmed, user} = storeToRefs(useAuthStore());
const {badges} = storeToRefs(useBadgeStore());

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

// Проверяем, является ли компания пользователя типом "Заказчик"
const isCustomerCompany = computed(() => {
  return user.value?.company?.company_type_id === CUSTOMER_COMPANY_TYPE_ID;
});

const cityTitle = computed(() => {
  if (!geoCity.value) return;
  if (!geoCityConfirmed.value) {
    return `Ваш регион ${geoCity.value.name}?`;
  }
  return `Ваш регион: ${geoCity.value.name}`;
});

const openCityDialog = () => {
  emitter.emit('cityDialog', true);
}
</script>

<style scoped lang="scss">
.container {
  padding-top: 7px;

  .el-link {
    font-size: 12px;

    &.bar-link {
      margin-left: 30px;
    }
  }

  .el-button {
    font-size: 12px;

    &:last-of-type {
      margin-left: 5px;
    }
  }
}

</style>