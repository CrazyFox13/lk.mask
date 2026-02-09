<template>
  <section class="bg">
    <div class="container text-block">
      <h1 class="t-h1">Аренда спестехники МАСК Групп</h1>
      <div class="t-h2">Быстрое оформление заявки, удобное согласование</div>
      <div class="t-h3">
        Собственный парк спецтехники и квалифицированные менеджеры <br class="hidden-sm-and-down"/>
        помогут быстро решить ваши задачи
      </div>
      <el-select :suffix-transition="false" :suffix-icon="SearchInput" @change="onSearch" placeholder="Выберете необходимую технику." v-model="vehicleGroupId">
        <el-option v-for="group in vehicleGroups" :key="group.id" :value="group.id" :label="group.title"/>
      </el-select>
    </div>
  </section>
</template>

<script setup lang="ts">

import {apiFetch, ref, useRouter} from "#imports";
import SearchInput from "~/components/Common/Icons/SearchInput.vue";

const vehicleGroupId = ref<number>();

const {vehicleGroups} = await apiFetch('vehicle-groups')

const router = useRouter();
const onSearch = () => {
  router.push(`/orders/create?vehicle_group_id=${vehicleGroupId.value}`)
}

</script>

<style scoped lang="scss">
.bg {
  background: url("~/assets/images/tracks_banner_mobile.svg") center center no-repeat;
  background-size: cover;
  height: 474px;
  display: flex;
  flex-flow: column;
  justify-content: center;
  align-items: center;
  text-align: center;

  .text-block {
    max-width: 700px;
    color: #FFFFFF;
    padding: 20px 0;

    .t-h1 {
      font-weight: 700;
      font-size: 28px;
      line-height: 34px;
      font-family: "Roboto Bold", sans-serif;
      margin-bottom: 14px;
      text-shadow: 
        0 2px 4px rgba(0, 0, 0, 0.9),
        0 4px 8px rgba(0, 0, 0, 0.7),
        0 0 20px rgba(0, 0, 0, 0.5),
        2px 2px 0 rgba(0, 0, 0, 0.3);
      letter-spacing: 0.5px;
    }

    .t-h2 {
      font-weight: 700;
      font-size: 18px;
      line-height: 24px;
      text-align: center;
      vertical-align: middle;
      margin-bottom: 14px;
      text-shadow: 
        0 2px 4px rgba(0, 0, 0, 0.9),
        0 4px 8px rgba(0, 0, 0, 0.7),
        0 0 20px rgba(0, 0, 0, 0.5),
        2px 2px 0 rgba(0, 0, 0, 0.3);
      letter-spacing: 0.3px;
    }

    .t-h3 {
      font-weight: 500;
      font-size: 14px;
      line-height: 20px;
      margin-bottom: 14px;
      text-shadow: 
        0 1px 3px rgba(0, 0, 0, 0.9),
        0 2px 6px rgba(0, 0, 0, 0.7),
        0 0 15px rgba(0, 0, 0, 0.5);
      letter-spacing: 0.2px;
    }
  }

  .el-select {
    width: 100%;
    max-width: 520px;
  }

  @media (min-width: 480px) {
    margin-top: -55px;
    background: url("~/assets/images/tracks_banner.png") center center no-repeat;
    background-size: cover;
    height: 604px;

    .text-block {
      margin: auto;
      padding: 30px 0;
    }
  }

  @media (min-width: 992px) {
    padding-top: 0;
    .text-block {
      transform: translate(0px, 0);
      padding: 40px 0;

      .t-h1 {
        font-weight: 700;
        font-size: 40px;
        line-height: 50px;
        text-shadow: 
          0 3px 6px rgba(0, 0, 0, 0.9),
          0 6px 12px rgba(0, 0, 0, 0.8),
          0 0 30px rgba(0, 0, 0, 0.6),
          3px 3px 0 rgba(0, 0, 0, 0.4);
        letter-spacing: 0.8px;
      }

      .t-h2 {
        font-weight: 700;
        font-size: 32px;
        line-height: 38px;
        text-align: center;
        vertical-align: middle;
        text-shadow: 
          0 3px 6px rgba(0, 0, 0, 0.9),
          0 6px 12px rgba(0, 0, 0, 0.8),
          0 0 30px rgba(0, 0, 0, 0.6),
          3px 3px 0 rgba(0, 0, 0, 0.4);
        letter-spacing: 0.5px;
      }

      .t-h3 {
        font-weight: 500;
        font-size: 16px;
        line-height: 22px;
        text-shadow: 
          0 2px 4px rgba(0, 0, 0, 0.9),
          0 4px 8px rgba(0, 0, 0, 0.7),
          0 0 20px rgba(0, 0, 0, 0.5);
        letter-spacing: 0.3px;
      }
    }
  }

  @media (min-width: 1960px) /*and (max-width: 2050px)*/
  {
    margin-top: 0;
    background-position: bottom;
    height: 560px;
  }

  /* @media (min-width: 2050px) {
     margin-top: 0;
     background-position: bottom;
   }*/
}
</style>