<template>
  <div class="footer">
    <div class="container">
      <el-row>
        <el-col :span="24" :lg="6">
          <h4>Мы в соцсетях</h4>
          <div class="d-flex justify-between">
            <el-link href="https://t.me/mack_group" target="_blank" class="social-icon">
              <SvgIcon name="telegram"/>
            </el-link>
            <!--<el-link class="social-icon">
              <SvgIcon name="youtube"/>
            </el-link>
            <el-link class="social-icon">
              <SvgIcon name="whatsapp"/>
            </el-link>
            <el-link class="social-icon">
              <SvgIcon name="vk"/>
            </el-link>-->
          </div>

          <SharePanel link="https://astt.su">
            <template #default>
              <el-button type="primary" class="mt-5 share-btn">
                Поделиться
                <SvgIcon class="text-black ml-2" name="share"/>
              </el-button>
            </template>
          </SharePanel>
        </el-col>
        <el-col :span="24" :lg="6">
          <h4>Документы</h4>
          <div class="flex flex-column align-items-start text-white">
            <nuxt-link class="el-link mb-3" to="/soglas/politika">
              Политика конфиденциальности
            </nuxt-link>
            <nuxt-link class="el-link" to="/soglas/soglzakaz">
              Пользовательское соглашение для заказчика
            </nuxt-link>
          </div>
        </el-col>
        <el-col :span="24" :lg="6">
          <h4>Участникам</h4>
          <div class="flex flex-column align-items-start text-white">
            <nuxt-link class="el-link mb-3" to="/faq/to-customers">
              Заказчикам
            </nuxt-link>
            <nuxt-link class="el-link mb-3" to="/faq">
              О сервисе
            </nuxt-link>
            <nuxt-link class="el-link" to="/download-app">
              Мобильное приложение
            </nuxt-link>
          </div>
        </el-col>
        <el-col :span="24" :lg="6">
          <h4>Скачать приложение</h4>
          <div class="flex align-items-center">
            <img alt="qr" class="mr-5 qr hidden-sm-and-down" :src="QR"/>
            <div class="flex flex-column">
              <GooglePlayButton class="mb-2"/>
              <AppStoreButton/>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup>
import SvgIcon from "../SvgIcon";
import QR from "~/assets/images/mob_qr_white.svg"
import GooglePlayButton from "../Common/GooglePlayButton";
import AppStoreButton from "../Common/AppStoreButton";
import SharePanel from "../Common/SharePanel";

</script>

<style lang="scss">
.footer {
  .qr {
    width: 90px;
    height: 90px;
  }

  .share-btn {
    padding: 6px 12px !important;
  }

  .social-icon {
    .el-link__inner {
      span {
        display: flex;
      }
    }
  }

  .el-col {
    margin-bottom: 32px;

    &:last-of-type {
      margin-bottom: 0;
    }

    @media (min-width: 1200px) {
      margin-bottom: 0;
    }
  }
}
</style>