<template>
  <div>
    <el-button link @click="onButtonClick">
      <SvgIcon name="filter" class="mr-2"/>
      Фильтры
    </el-button>

    <client-only>
      <el-dialog
          v-model="dialog"
          :fullscreen="isMobile"
          width="570"
          :before-close="handleClose"
          :show-close="false"
      >
        <template #header="{close}">
          <div class="dialog-head">
            <el-button class="close-btn text-black" circle text @click="close">
              <SvgIcon name="close"/>
            </el-button>
            <div class="text-black modal-window-title text-center m-0">Фильтры</div>
            <el-button link size="small" class="text-gray reset-btn" @click="reset()">Сбросить</el-button>
          </div>
        </template>

        <FilterForm ref="form" v-model="value"/>

        <el-button class="btn-submit" type="primary" @click="submit()">Применить</el-button>
      </el-dialog>
    </client-only>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import FilterForm from "~/components/Company/FilterForm.vue";
import {type ICompanyFilter} from "~/stores/company";
import {ref} from "#imports";


const {isMobile} = storeToRefs(useDeviceStore())
const dialog = ref(false);
const emit = defineEmits(['update:modelValue', 'search']);
const props = defineProps(['modelValue']);
const form = ref<any>(null);

const value = ref<ICompanyFilter>(props.modelValue);

const onButtonClick = () => {
  dialog.value = true;
}

const handleClose = () => {
  dialog.value = false;
}

const reset = () => {
  value.value = {
    cities_id: [],
    rating: null
  };
}

const submit = () => {
  emit('update:modelValue', value.value);
  emit("search");
  dialog.value = false;
}

</script>

<style scoped lang="scss">
.btn-submit {
  width: 100%;
  margin-top: 20px;
  @media (min-width: 992px) {
    max-width: 180px;
  }
}

.switcher {
  p {
    margin: 0;
  }
}

</style>