<template>
  <div>
    <Preloader v-if="loading"/>
    <el-card v-else-if="photoGroups.length>0">
      <div class="text-h5 mb-10">Выберите альбом</div>
      <el-select class="mb-10" placeholder="Выберите альбом" v-model="photoGroupId">
        <el-option
            v-for="group in photoGroups"
            :key="group.id"
            :label="group.title"
            :value="group.id"
        />
      </el-select>

      <div v-if="selectedGroup" class="flex flex-wrap photos-flex">
        <CompanyPortfolioPhoto
            v-for="(photo,i) in selectedGroup.photos"
            :key="photo.id"
            :photo="photo"
            :index="i"
            :totalCount="selectedGroup.photos.length"
        />
      </div>
    </el-card>

    <ContentNotFound v-else/>
  </div>
</template>

<script setup lang="ts">

import ContentNotFound from "~/components/Common/ContentNotFound.vue";
import {apiFetch} from "~/composables/apiFetch";
import {computed, nextTick, useAsyncData} from "#imports";
import CompanyPortfolioPhoto from "~/components/Company/CompanyPortfolioPhoto.vue";
import Preloader from "~/components/Preloader.vue";

const loading = ref(false);
const route = useRoute();
const companyId = Number(route.params.id);
const photoGroups = ref<any[]>([]);
const photoGroupId = ref<number>();

const getPhotoGroups = async () => {
  loading.value = true;
  const data = await apiFetch(`companies/${companyId}/photo-groups`, {}, true);
  photoGroups.value = data.photoGroups;
  if (photoGroups.value.length > 0) {
    photoGroupId.value = photoGroups.value[0].id;
  }
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getPhotoGroups());

const selectedGroup = computed(() => {
  if (!photoGroupId.value) return;
  return photoGroups.value.find((group: any) => group.id === photoGroupId.value);
});

</script>

<style scoped lang="scss">
.el-select {
  width: 100%;
  @media (min-width: 992px) {
    width: auto;
  }
}

.photos-flex {
  row-gap: 2px;
  column-gap: 2px;
}
</style>