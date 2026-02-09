<template>
  <div>
    <TopBar class="top-bar"/>
    <HeaderSection class="header-section"/>
    <MobileHeader class="mobile-header"/>
    <div class="bg page">
      <div class="container">
        <h1 class="text-h1 text-white">
          404 ошибка. <br/>
          Страница не найдена
        </h1>
        <p class="text-white">Запрашиваемая страница не существует или была удалена</p>
        <nuxt-link to="/" class="el-button el-button--primary">Вернуться на главную</nuxt-link>
      </div>
    </div>
    <FooterSection class="footer-section"/>
    <MobileMenu class="hidden-md-and-up"/>
  </div>
</template>

<script setup lang="ts">
import TopBar from "~/components/Layout/TopBar.vue";
import HeaderSection from "~/components/Layout/HeaderSection.vue";
import MobileAppBar from "~/components/Layout/MobileAppBar.vue";
import MobileHeader from "~/components/Layout/MobileHeader.vue";
import FooterSection from "~/components/Layout/FooterSection.vue";
import MobileMenu from "~/components/Layout/MobileMenu.vue";
</script>

<style scoped lang="scss">
.bg {
  background: url("~/assets/images/error.png") center center no-repeat;
  background-size: cover;
  height: 510px;
  background-position: 60%;
  padding-top: 56px;
  text-align: center;

  h1 {
    margin-bottom: 10px;
  }

  p {
    margin-bottom: 20px;
  }

  .el-button {
    padding: 12px 43px;
  }

  @media (min-width: 992px) {
    background-position: center;
    text-align: left;
    padding-top: 149px;

    h1 {
      margin-bottom: 20px;
    }

    p {
      margin-bottom: 30px;
    }
  }

}
</style>