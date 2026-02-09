<template>
  <section class="benefits-section section bg-gray">
    <div class="container">
      <h2 class="text-h2 text-center text-black">Наши преимущества</h2>
      <Splide
          ref="splide"
          @splide:moved="onChanged"
          :has-track="false"
          :options="{arrows:false, perPage: isMobile ? 1 : 3,pagination:false,type:'loop' }"
      >
        <SplideTrack>
          <SplideSlide v-for="(item,k) in items" :key="k">
            <div class="carousel-item-content flex">
              <img :src="item.img" :alt="item.title" class="carousel-img mr-3"/>
              <div class="flex flex-column">
                <div class="text-h3 text-black">{{ item.title }}</div>
                <p class="text-gray text-subtitle">{{ item.subtitle }}</p>
              </div>
            </div>
          </SplideSlide>
        </SplideTrack>
        <div class="splide__arrows">
          <el-button circle class="arrow" @click="onBack()">
            <SvgIcon name="chevron-left"/>
          </el-button>
          <el-button circle class="arrow" @click="onForward()">
            <SvgIcon name="chevron-right"/>
          </el-button>
        </div>
      </Splide>
      <el-card shadow="never" v-if="data">
        <el-row class="text-center">
          <el-col :span="8">
            <div class="text-h4 text-black">
              <span class="stats-value">{{data.companies}}</span>
              компаний
            </div>
          </el-col>
          <el-col :span="8">
            <div class="text-h4 text-black">
              <span class="stats-value">{{data.users}}</span>
              поставщиков
            </div>
          </el-col>
          <el-col :span="8">
            <div class="text-h4 text-black">
              <span class="stats-value">{{data.orders}}</span>
              заявок
            </div>
          </el-col>
        </el-row>
      </el-card>
    </div>
  </section>
</template>

<script setup lang="ts">
import SafetyImg from '~/assets/images/benefit_safety.png'
import RatingImg from '~/assets/images/benefit_rating.png'
import DBImage from '~/assets/images/benefit_database.png'
import OrderImg from '~/assets/images/benefits_orders.svg'
import NoPplImg from '~/assets/images/benefits_no_ppl.svg'
import {Splide, SplideSlide, SplideTrack} from '@splidejs/vue-splide';
import '@splidejs/vue-splide/css';
import {apiFetch, ref} from "#imports";
import {storeToRefs} from "pinia";
import SvgIcon from "~/components/SvgIcon.vue";
import {useDeviceStore} from "~/stores/device";


const items = [
  {
    img: SafetyImg,
    title: 'Безопасность',
    subtitle: "Реальные компании, проверенные поставщики и заказчики"
  },
  {
    img: RatingImg,
    title: 'Реальный рейтинг',
    subtitle: "Без накруток, все претензии рассматриваются публично"
  },
  {
    img: DBImage,
    title: 'База компаний',
    subtitle: "Несколько сотен компаний с собственном парком техники"
  },
  {
    img: OrderImg,
    title: 'Постоянные заказы',
    subtitle: "Ежедневные обновления, актуальные заявки на спецтехнику."
  },
  {
    img: NoPplImg,
    title: 'Без посредников',
    subtitle: "Работа напрямую, заказчики и собственники техники."
  },
];

const {data} = await apiFetch('index');

const {isMobile} = storeToRefs(useDeviceStore());
const step = ref(0);
const splide = ref();


const onBack = () => {
  step.value--;
  splide.value.splide.go(step.value)
}

const onForward = () => {
  step.value++;
  splide.value.splide.go(step.value)
}

const onChanged = (e: Event, index: number, prevIndex: number) => {
  step.value = index;
}

</script>

<style lang="scss">
.benefits-section {

  h2 {
    margin-bottom: 32px;
  }

  .splide {
    margin-left: -10px;
    margin-right: -10px;
    margin-bottom: 32px;

    .arrow {
      align-items: center;
      border: 0;
      cursor: pointer;
      display: flex;
      justify-content: center;
      padding: 0;
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 1;
      width: 30px;
      height: 30px;
      text-align: center;
      border-radius: 100px;
      box-shadow: 3px 3px 20px rgb(23 26 27 / 10%);
      border: none;
      background: #FFFFFF;

      &:last-of-type {
        right: 0;
      }
    }

    .splide__list {
      height: 74px;

      .carousel-item-content {
        width: 300px;
        margin: auto;

        .carousel-img {
          width: auto;
          height: 74px;
        }

        .text-h3 {
          margin-bottom: 8px;
          margin-top: 0;
        }

        p {
          margin: 0;
        }
      }
    }
  }

  .el-card {

    .text-h4 {
      margin: 0;
      display: flex;
      flex-direction: column;

      span {
        font-weight: 700;
        font-size: 26px;
        line-height: 32px;
      }
    }
  }

  .stats-value {
    color: #fab80f;
  }

  @media (min-width: 992px) {

    .splide {
      height: 80px;
      margin-bottom: 70px;

      .carousel-item-content {
        width: 364px;
        margin: auto;

        .carousel-img {
          width: auto;
          height: 82px;
        }
      }
    }

    .el-card {

      .text-h4 {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: row;

        span {
          font-size: 50px;
          line-height: 50px;
          margin-right: 10px;
        }
      }
    }
  }
}
</style>