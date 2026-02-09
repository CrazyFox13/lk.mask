<template>
  <div>
    <div class="container mob-container flex justify-between">
      <nuxt-link to="/">
        <img :src="Logo" alt="Логотип" class="m-logo"/>
      </nuxt-link>
      <div class="flex">
        <nuxt-link v-if="user" class="el-link" to="/favorites/orders">
          <SvgIcon name="star-outline"/>
        </nuxt-link>
        <el-link @click="openCityDialog()">
          <SvgIcon name="send"/>
        </el-link>
        <el-link @click="onSearchButtonClick()">
          <SvgIcon name="search"/>
        </el-link>
      </div>
    </div>

    <client-only>
      <el-dialog v-model="dialog" fullscreen>
        <template #header>
          <img :src="Logo" alt="Логотип" class="m-logo"/>
        </template>
        <SearchInput
            class="mb-0"
            placeholder="Введите название / номер компании"
            v-model="search"
            @submit="onSearch"
        />
      </el-dialog>
    </client-only>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "../SvgIcon.vue";
import Logo from "~/assets/images/logo-mask-group.png.jpg";

import {nextTick, ref, useRoute, useRouter} from '#imports'
import SearchInput from "~/components/Common/Forms/SearchInput.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";

const {user} = storeToRefs(useAuthStore());

const dialog = ref(false);
const onSearchButtonClick = () => {
  dialog.value = true;
}

const router = useRouter();
const route = useRoute();
const search = ref(route.query.search ? route.query.search : '');
const onSearch = () => {
  router.push(`/companies?search=${search.value}`);
  nextTick(()=>{
    dialog.value = false;
  })
}

const openCityDialog = () => {
  emitter.emit('cityDialog', true);
}
</script>

<style lang="scss">
.mob-container {
  padding-top: 16px !important;

  .el-link {
    margin-left: 16px;
  }
}

.m-logo {
  width: 120px;
  height: auto;
  display: block;
}
</style>