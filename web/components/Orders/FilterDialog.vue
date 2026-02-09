<template>
  <div>
    <slot name="trigger" :action="onButtonClick">
      <el-button link @click="onButtonClick">
        <SvgIcon name="filter" class="mr-2"/>
        Фильтры
      </el-button>
    </slot>

    <client-only>
      <Teleport to="body">
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

          <FilterForm
              ref="form"
              v-model="value"
              :hide-favorite="hideFavorite"
              @save-filter="onSaveFilters"
              :filter-changed="filterChanged"
              @show-filters="onShowFilters"
              :filters-exists="filtersExists"
          />

          <el-button class="btn-submit" type="primary" @click="submit()">Применить</el-button>
        </el-dialog>
      </Teleport>
    </client-only>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import FilterForm from "~/components/Orders/FilterForm.vue";
import {type IOrderFilter} from "~/stores/order";
import {useRoute, watch, ref} from "#imports";


const {isMobile} = storeToRefs(useDeviceStore())
const dialog = ref(false);
const emit = defineEmits(['update:modelValue', 'search', 'saveFilter', 'showFilters']);
const props = defineProps(['modelValue', 'hideFavorite', 'filterChanged', 'filtersExists']);
const form = ref<any>(null);

const value = ref<IOrderFilter>(props.modelValue);
const route = useRoute();

watch(route, () => {
  dialog.value = false;
}, {deep: true})

const onButtonClick = () => {
  dialog.value = true;
}

const handleClose = () => {
  dialog.value = false;
}

const reset = () => {
  value.value = {
    cities_id: [],
    shifts: '',
    date: undefined,
    amount_by_agreement: false,
    amount_with_vat: false,
    amount_cash: false,
    with_company: false,
    favorite: false,
  };
}

const submit = () => {
  emit('update:modelValue', value.value);
  emit("search");
  dialog.value = false;
}

const onShowFilters = () => {
  emit('showFilters');
  dialog.value = false;
}

const onSaveFilters = () => {
  emit('saveFilter');
  dialog.value = false;
}


watch(props, () => {
  value.value = props.modelValue;
}, {deep: true})
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