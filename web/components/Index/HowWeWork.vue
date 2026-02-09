<template>
  <section class="how-we-work section">
    <div class="container">
      <el-row>
        <el-col :span="24" :md="12">
          <el-card shadow="never">
            <h4 class="text-h2 text-black">Как мы работаем</h4>
            <div class="flex flex-column how-list">
              <div class="flex how-item">
                <div class="how-number text-yellow">1</div>
                <SvgIcon class="how-icon" name="how_register" />
                <h5 class="how-title text-black">Регистрация</h5>
              </div>
              <div class="flex how-item">
                <div class="how-number text-yellow">2</div>
                <SvgIcon class="how-icon" name="how_confirmation" />
                <h5 class="how-title text-black">Подтверждение компании</h5>
              </div>
              <div class="flex how-item">
                <div class="how-number text-yellow">3</div>
                <SvgIcon class="how-icon" name="how_work" />
                <h5 class="how-title text-black">Работа по заявки</h5>
              </div>
              <div class="flex how-item">
                <div class="how-number text-yellow">4</div>
                <SvgIcon class="how-icon" name="how_like" />
                <h5 class="how-title text-black">Довольные клиенты и поставщики</h5>
              </div>
            </div>
            <nuxt-link to="/orders/create" class="el-button el-button--primary create-order">
              Оставить заявку
            </nuxt-link>
          </el-card>
        </el-col>
        <el-col :span="24" :md="12" class="iframe-container">
          <DelayHydration>
            <iframe width="560" height="315" src="https://www.youtube.com/embed/_PQXrYn8MKA" title="YouTube video player"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture;"
              allowfullscreen></iframe>
          </DelayHydration>
        </el-col>
      </el-row>
    </div>
  </section>
</template>

<script>
import SvgIcon from "../SvgIcon";

export default {
  name: "HowWeWork",
  components: { SvgIcon }
}
</script>

<style scoped lang="scss">
.how-we-work {
  background: #F3F5F9;
  /*padding-top: 32px;
  padding-bottom: 32px;*/

  /*@media (min-width: 992px) {
    padding-top: 80px;
    padding-bottom: 80px;
  }*/

  /*.el-card{
    border-radius: 10px;
  }*/

  .how-item {
    margin-bottom: 20px;
    align-items: center;

    &:nth-child(1) {
      order: 1
    }

    &:nth-child(2) {
      order: 2
    }

    &:nth-child(3) {
      order: 3
    }

    &:nth-child(4) {
      order: 4
    }

    .how-number {
      font-weight: 800;
      font-size: 26px;
      line-height: 20px;
      margin-right: 16px;
    }

    .how-icon {
      margin-right: 16px;
    }

    .how-title {
      font-weight: 400;
      font-size: 14px;
      line-height: 20px;
      margin: 0;
    }
  }

  .create-order {
    padding: 11px 20px;
    width: 100%;
  }

  iframe {
    border-radius: 10px;
    margin-top: 20px;
    width: 100%;
    height: auto;
    min-height: 183px;
  }

  @media (min-width: 992px) {
    background: url('~/assets/images/how_we_work_bg.png');
    background-size: cover;
    background-color: #F3F5F9;
    /*background-position: center bottom;*/

    .el-card {
      padding: 30px;

      .text-h2 {
        font-size: 32px;
        line-height: 38px;
        margin-bottom: 30px;
      }

      .how-list {
        flex-wrap: wrap;
        flex-direction: row;
        margin-bottom: 22px;

        .how-item {
          width: 50%;

          .how-title {
            font-size: 16px;
            line-height: 24px;
          }

          &:nth-child(2) {
            order: 3
          }

          &:nth-child(3) {
            order: 2
          }
        }
      }

      .create-order {
        max-width: 150px;
        padding: 12px 20px;
      }
    }

    .iframe-container {
      padding-left: 30px;

      iframe {
        margin-top: 0;
        height: 314px;
      }
    }
  }
}
</style>