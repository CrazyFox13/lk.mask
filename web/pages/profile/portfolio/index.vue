<template>
  <div>
    <NoApprovedCompany v-if="noApprovedCompany()"/>
    <el-card v-else>
      <div class="flex justify-between align-items-center">
        <div class="text-h3">Альбомы</div>
        <nuxt-link class="el-link el-button el-button--text text-black" to="/profile/portfolio/create">
          <span class="hidden-sm-and-down">Добавить альбом &nbsp;</span>
          <SvgIcon name="add"/>
        </nuxt-link>
      </div>
      <el-divider/>
      <div class="flex justify-center" v-if="photoGroups.length===0">
        <div class="text-center">
          <el-image :src="NotFoundImage" alt="Ничего не найдено"/>
          <div class="text-h4">Фотографий пока нет</div>
        </div>
      </div>
      <div v-else>
        <div v-for="group in photoGroups" :key="group.id" class="mb-30">
          <div class="flex justify-between mb-20">
            <div class="text-h4">{{ group.title }}</div>
            <div class="actions">
              <nuxt-link class="el-button el-button--info" :to="`/profile/portfolio/${group.id}`">
                <SvgIcon name="pencil"/>
              </nuxt-link>
              <el-button type="info" @click="destroy(group)">
                <SvgIcon name="trash"/>
              </el-button>
            </div>
          </div>
          <div class="photos-list">
            <PreviewPhotoItem
                v-for="(photo,k) in group.photos" :key="k"
                :photo="photo.url"
                :viewable="true"
                @open="open(group,photo)"
            />
          </div>
        </div>
      </div>
    </el-card>

    <ViewGroupDialog v-if="viewDialog" @close="viewDialog=false;">
      <template #content>
        <ViewSlider :list="viewGroup.photos"/>
      </template>
    </ViewGroupDialog>
  </div>
</template>

<script setup lang="ts">

import NotFoundImage from '~/assets/images/content_no_found.png';
import NoApprovedCompany from "~/components/Profile/NoApprovedCompany.vue";
import {useAuthStore} from "~/stores/user";
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, ref, definePageMeta} from "#imports";
import {storeToRefs} from "pinia";
import PreviewPhotoItem from "~/components/Profile/Portfolio/PreviewPhotoItem.vue";
import ViewGroupDialog from "~/pages/profile/portfolio/ViewGroupDialog.vue";
import ViewSlider from "~/pages/profile/portfolio/ViewSlider.vue";

definePageMeta({
  middleware: 'block-customer-portfolio'
});

interface IPhotoGroup {
  id: number
  title: string
  company_id: number
  photos: IPhoto[]
}

interface IPhoto {
  id: number
  photo_group_id: number
  url: string
}

const {user} = storeToRefs(useAuthStore());
const companyId = user.value!.company_id;
const {noApprovedCompany} = useAuthStore();
const photoGroups = ref<IPhotoGroup[]>([]);
const viewDialog = ref(false);
const viewGroup = ref<IPhotoGroup>();
const activePhoto = ref<IPhoto>();

if (!noApprovedCompany()){
  const data = await apiFetch(`companies/${companyId}/photo-groups`);
  photoGroups.value = data.photoGroups
}


const destroy = (group: IPhotoGroup) => {
  apiFetch(`companies/${companyId}/photo-groups/${group.id}`, {
    method: "DELETE"
  }).then(() => {
    photoGroups.value.splice(photoGroups.value.indexOf(group), 1);
  })
}

const open = (group: IPhotoGroup, photo: IPhoto) => {
  viewGroup.value = group;
  activePhoto.value = photo;
  viewDialog.value = true;
}

</script>

<style scoped lang="scss">
.btn-add {
  padding: 0;
}

.actions {
  .el-button {
    width: 32px;
    height: 32px;
    padding: 0;
  }
}

.photos-list {
  display: flex;
  column-gap: 2px;
  flex-wrap: wrap;
}
</style>