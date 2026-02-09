<template>
  <div class="group-item">
    <div class="logo">
      <img v-if="group.logo" :src="group.logo" :alt="group.title"/>
    </div>
    <div class="text-h4 text-black">{{ group.title }}</div>
    <div v-if="group.types_count>0 && !isSupplierCompany" class="types-list">
      <ol>
        <li v-for="type in group.types.slice(0,4)" :key="type.id">
          <nuxt-link :to="`/orders/create?vehicle_group_id=${group.id}&vehicle_type_id=${type.id}`" class="el-link text-gray">{{ type.title }}</nuxt-link>
        </li>
      </ol>

      <nuxt-link
          :to="`/orders/create?vehicle_group_id=${group.id}`"
          class="border-link el-button el-button--primary is-plain prime-link"
      >
        Создать заявку
      </nuxt-link>
    </div>
  </div>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {computed} from "#imports";

const {group} = defineProps(['group'])

// ID типа компании "Заказчик" = 3
const CUSTOMER_COMPANY_TYPE_ID = 3;

const authStore = useAuthStore()
const {user} = storeToRefs(authStore);

// Проверяем, является ли компания пользователя типом "Поставщик" (не "Заказчик")
const isSupplierCompany = computed(() => {
  return user.value?.company?.company_type_id && user.value.company.company_type_id !== CUSTOMER_COMPANY_TYPE_ID;
});
</script>

<style lang="scss">
.group-item {
  height: 280px;
  display: flex;
  flex-direction: column;
  //margin-right: 20px;

  @media (min-width: 992px) {
    height: 315px;
    margin-bottom: 40px;
  }

  .logo {
    height: 48px;
    width: 48px;

    @media (min-width: 992px) {
      height: 56px;
      width: 56px;
    }

    img {
      height: 100%;
    }
  }

  .text-h4 {
    margin-bottom: 10px;
    margin-top: 30px;

    @media (min-width: 992px) {
      margin-bottom: 20px;
    }
  }

  .types-list {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    ol {
      list-style-type: none;
      padding: 0;

      li {
        font-size: 14px;
        line-height: 22px;
        margin-bottom: 3px;

        a {
          text-decoration: underline;
          max-width: 100%;

          span {
            display: inline-block;
            text-overflow: ellipsis;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
          }
        }
      }
    }
  }

  .border-link {
    padding: 9px 18.5px;
    font-weight: 500;
    font-size: 13px;
    line-height: 14px;
    margin-top: 20px;
    display: block;
    text-align: center;

    @media (min-width: 992px) {
      max-width: 150px;
    }
  }
}
</style>