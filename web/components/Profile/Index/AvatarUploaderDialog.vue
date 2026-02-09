<template>
  <client-only>
    <el-dialog
        v-model="dialog"
        :fullscreen="isMobile"
        width="570"
        :before-close="handleClose"
        :show-close="false"
        class="avatar-dialog"
    >
      <template #header="{close}">
        <div class="dialog-head">
          <el-button class="close-btn text-black" circle text @click="close">
            <SvgIcon name="close"/>
          </el-button>
          <div class="text-black modal-window-title text-center mb-10">Фотограция профиля</div>
          <p class="text-gray text-center mt-0">Выбранная область будет показываться на вашей странице.</p>
        </div>
      </template>

      <div class="crop-area">
        <el-button class="crop-btn" @click.prevent="cropping=true" circle>
          <SvgIcon name="pencil"/>
        </el-button>
        <label class="text-center">
          <input v-if="!cropping" @change="fileSelected" ref="fileInput" type="file" accept="image/*"/>
          <img v-if="!!blob" :src="blob" id="image" alt="avatar"/>
        </label>
      </div>

      <div class="text-center mt-20">
        <el-button
            class="btn-submit"
            :type="loading?'info':'primary'"
            @click="submit()"
            :disabled="loading"
        >
          Сохранить
        </el-button>
        <el-button
            :disabled="!cropping"
            class="btn-submit"
            :type="cropping?'primary':'info'"
            plain
            @click="cropping=false"
        >
          Отменить
        </el-button>
      </div>
    </el-dialog>
  </client-only>
</template>

<script setup lang="ts">
import 'cropperjs/dist/cropper.css';
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import SvgIcon from "~/components/SvgIcon.vue";
import Cropper from 'cropperjs';
import {nextTick, onMounted, ref, watch} from "#imports";
import FileUploader, {type IUploadedFile} from "~/composables/fileUploader";
import {useAuthStore} from "~/stores/user";

const emit = defineEmits(['close']);
const {isMobile} = storeToRefs(useDeviceStore());
const dialog = ref(true);
const cropping = ref(false);
const authStore = useAuthStore();
const {setAvatar} = authStore;
const {user} = storeToRefs(authStore);
const avatar = ref<string | null>(user.value!.avatar);
const blob = ref<string>();
const cropper = ref();
const loading = ref(false);

const handleClose = () => {
  dialog.value = false;
  emit('close')
}

const getRoundedCanvas = (sourceCanvas: HTMLCanvasElement) => {
  const canvas = document.createElement('canvas');
  const context = <CanvasRenderingContext2D>canvas.getContext('2d');
  const width = sourceCanvas.width;
  const height = sourceCanvas.height;

  canvas.width = width;
  canvas.height = height;
  context.imageSmoothingEnabled = true;
  context.drawImage(sourceCanvas, 0, 0, width, height);
  context.globalCompositeOperation = 'destination-in';
  context.beginPath();
  context.arc(width / 2, height / 2, Math.min(width, height) / 2, 0, 2 * Math.PI, true);
  context.fill();
  return canvas;
}

const submit = () => {
  loading.value = true;

  const croppedCanvas = cropper.value.getCroppedCanvas();
  const roundedCanvas = getRoundedCanvas(croppedCanvas);
  roundedCanvas.toBlob((croppedBlob: Blob | null) => {
    if (!croppedBlob) return;

    const modifiedData = new Date().getTime();
    const newFile = new File([croppedBlob], `${modifiedData}.png`, {
      type: "image/png",
      lastModified: modifiedData,
    });

    const uploader = new FileUploader();
    uploader.on('uploaded', async (file: IUploadedFile) => {
      cropping.value = false;
      avatar.value = file.url;

      const res = await fetch(file.url);
      const data = await res.blob();
      const reader = new FileReader();
      reader.onload = function (e: any) {
        blob.value = e.target.result;
      }
      reader.readAsDataURL(data);

      await setAvatar(<string>avatar.value);
      loading.value = false;
      handleClose();
    })

    uploader.uploadFile(newFile);
  });
}

const fileSelected = async (e: any) => {
  const files = e.target.files;
  if (files.length === 0) return;
  const file = files[0];

  const img = <HTMLImageElement>document.getElementById('image');
  if (!img) return;
  readFile(file, true);
}

const readFile = (data: Blob, crop = false) => {
  const reader = new FileReader();
  reader.onload = function (e: any) {
    blob.value = e.target.result;
    if (crop) {
      nextTick(() => {
        cropping.value = true;
      });
    }
  }
  reader.readAsDataURL(data);
}

watch(cropping, (v) => {
  v ? createCropper() : destroyCropper();
});

const destroyCropper = () => {
  if (cropper.value) {
    cropper.value.destroy();
  }
}

const createCropper = () => {
  const image = <HTMLImageElement>document.getElementById('image');
  if (!image) return;
  destroyCropper();
  cropper.value = new Cropper(image, {
    aspectRatio: 1,
    viewMode: 1,
  });
}

const init = () => {
  let url = avatar.value;
  if (url) {
    fetch(url, {
      credentials: 'include'
    }).then(r => r.blob().then(readFile))
  } else {
    // todo
    blob.value = DefaultAvatar;
  }

}

onMounted(() => {
  nextTick(() => {
    init();
  })
})
</script>

<style lang="scss">
.avatar-dialog {
  input {
    display: none;
  }

  .crop-area {
    position: relative;

    label {
      display: block;
      cursor: pointer;

      img {
        max-width: 100%;
      }
    }

    .crop-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      background-color: #E8EBF1;
      color: #424242;
    }
  }


  .container {
    margin: 20px auto;
    max-width: 640px;
  }

  img {
    max-width: 100%;
  }

  .cropper-view-box,
  .cropper-face {
    border-radius: 50%;
  }

  /* The css styles for `outline` do not follow `border-radius` on iOS/Safari (#979). */
  .cropper-view-box {
    outline: 0;
    box-shadow: 0 0 0 1px #39f;
  }
}
</style>