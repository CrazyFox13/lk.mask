<template>
  <el-card>
    <div class="flex align-items-center title">
      <nuxt-link class="el-button el-button--info link-circle mr-5" to="/profile/portfolio">
        <SvgIcon name="back"/>
      </nuxt-link>
      <div class="text-h3">Новый альбом</div>
    </div>

    <div class="input-title mb-10">Название альбома*</div>
    <TextInput
        v-model="photoGroup.title"
        placeholder="Введите название"
        style="max-width: 395px"
        :error="errors.title"
    />
    <div class="photos-list">
      <PreviewPhotoItem
          v-for="(photo,k) in photoGroup.photos" :key="k"
          :photo="photo"
          @deleted="photoGroup.photos.splice(k,1)"
          :deletable="true"
      />
    </div>
    <FileUploader
        @file_data="fileUploaded"
        class="mt-20"
        label="Добавить фотографии"
        icon="circle-plus"
        :multiple="true"
    />

    <el-button
        class="mt-30 submit"
        :disabled="!isFormReady"
        :type="isFormReady?'primary':'info'"
        @click="submit()"
    >Создать альбом
    </el-button>
  </el-card>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import TextInput from "~/components/Common/Forms/TextInput.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import {apiFetch, computed, useRouter, definePageMeta} from "#imports";
import FileUploader from "~/components/Common/Forms/FileUploader.vue";
import PreviewPhotoItem from "~/components/Profile/Portfolio/PreviewPhotoItem.vue";

definePageMeta({
  middleware: 'block-customer-portfolio'
});

const {user} = storeToRefs(useAuthStore());

const photoGroup = ref({
  title: "",
  company_id: user.value!.company_id,
  photos: [] as any[]
});

const errors = ref({});

const isFormReady = computed(() => {
  return photoGroup.value.title.length > 0;
});

const router = useRouter();

const fileUploaded = (file: any) => {
  photoGroup.value.photos.push(file.url);
}

const submit = () => {
  errors.value = {}
  apiFetch(`companies/${user.value!.company_id}/photo-groups`, {
    method: "POST",
    body: photoGroup.value,
  }).then(() => {
    router.push('/profile/portfolio')
  }).catch(({body}) => {
    errors.value = body.errors;
  })
}
</script>

<style scoped lang="scss">

.title {
  margin-bottom: 20px;
  @media (min-width: 992px) {
    margin-bottom: 30px;
  }
}

.submit {
  width: 100%;

  @media (min-width: 992px) {
    max-width: 250px;
  }
}

.photos-list {
  display: flex;
  column-gap: 2px;
  flex-wrap: wrap;
}
</style>