<template>
  <div class="bg">
    <div class="texts d-flex flex-column">
      <div class="t-h3 text-black">Все профессионалы <br/> в одном приложении</div>
      <p class="text-gray hidden-md-and-up">Скачайте приложение — с ним ещё <br/> удобнее искать проверенных поставщиков
      </p>
      <p class="text-gray hidden-sm-and-down">Наведите камеру телефона на QR-код и скачайте приложение <br/> — с ним ещё
        удобнее искать проверенных подрядчиков.</p>
      <div class="buttons flex">
        <div class="qr-card hidden-sm-and-down">
          <img alt="qr" :src="MobileQR" class=" qr"/>
        </div>
        <GooglePlayButton/>
        <AppStoreButton/>
      </div>
    </div>
    <nuxt-img format="webp" class="app-img" alt="Мобильное приложение" src="/images/af_513.png"/>
  </div>
</template>

<script setup>
import GooglePlayButton from "~/components/Common/GooglePlayButton";
import AppStoreButton from "~/components/Common/AppStoreButton";
import MobileQR from '~/assets/images/mob_qr.png';
</script>

<style scoped lang="scss">
.bg {
  background: #DFEDF0;
  border-radius: 20px;
  padding: 30px;
  text-align: center;
  margin-top: 48px;
  display: flex;
  flex-direction: column;
  position: relative;
  @media (min-width: 992px) {
    margin-top: 80px;
    text-align: left;
  }


  .t-h3 {
    font-weight: 700;
    font-size: 26px;
    line-height: 32px;
    margin-top: 0;
    margin-bottom: 12px;
    font-family: "Roboto Bold",serif;
  }

  p {
    font-weight: 400;
    font-size: 14px;
    line-height: 20px;
    margin: 0;
  }

  .buttons {
    margin-top: 24px;
    justify-content: space-around;
  }

  .app-img {
    margin-bottom: -30px;
    width: 261px;
    height: 216px;
   // margin-top: 25px;
  }

  @media (min-width: 992px) {
    flex-direction: row;
    padding: 106px 117px;

    .t-h3 {
      font-size: 40px;
      line-height: 50px;
    }

    p {
      font-size: 16px;
      line-height: 24px;
    }

    .app-img {
      width: 621px;
      height: 500px;
      flex: 1;
      position: absolute;
      bottom: 0;
      margin: 0;
      right: 0;
    }

    .texts, .app-img {
      width: 50%;

      .buttons {
        justify-content: flex-start;

        & > * {
          margin-right: 20px;

          &:last-child {
            margin-right: 0;
          }
        }

        .qr-card {
          box-shadow: 6px 6px 24px 2px rgba(23, 26, 27, 0.14);
          border-radius: 10px;
          //padding: 15px;
          width: 90px;
          display: flex;
          height: 90px;
          background-color: #FFFFFF;
        }
      }
    }
  }
}
</style>