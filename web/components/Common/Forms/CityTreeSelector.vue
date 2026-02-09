<template>
  <el-dialog
      v-model="dialog"
      :fullscreen="isMobile"
      width="570"
      :show-close="false"
      :append-to-body="true"
      :before-close="onClose"
  >
    <template #header="{close}">
      <div class="dialog-head">
        <el-button class="close-btn text-black hidden-md-and-up" circle text @click="onClose()">
          <SvgIcon name="chevron-left"/>
        </el-button>

        <div class="text-black modal-window-title m-0 text-center">Регион</div>
        <el-button link size="small" class="text-gray reset-btn hidden-md-and-up" @click="reset()">Сбросить</el-button>
        <el-button class="close-btn text-black hidden-sm-and-down" circle text @click="onClose()">
          <SvgIcon name="close"/>
        </el-button>
      </div>
    </template>
    <SearchInput v-model="search" placeholder="Введите название региона или города"/>

    <el-tree
        class="tree"
        :data="items"
        show-checkbox
        node-key="key"
        default-expand-all
        :props="defaultProps"
        ref="selector"
        @check-change="handleCheckChange"
        :filterNodeMethod="onFilter"
    />
    <div class="flex">
      <el-button class="btn-submit" type="primary" @click="submit()">Применить</el-button>
      <el-button class="btn-submit hidden-sm-and-down" type="primary" plain @click="reset()">Сбросить</el-button>
    </div>
  </el-dialog>
</template>

<script setup lang="ts">

import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import SvgIcon from "~/components/SvgIcon.vue";
import { nextTick, onMounted, watch} from "#imports";
import SearchInput from "~/components/Common/Forms/SearchInput.vue";
import debounce from "lodash.debounce";

const defaultProps = {
  children: 'cities',
  label: 'title',
}
const {isMobile} = storeToRefs(useDeviceStore())
const props = defineProps(['items', 'regionsId', 'modelValue', 'shown']);
const emit = defineEmits(['update:modelValue', 'close', 'regionsUpdate']);
const selectedRegionsId = ref(props.regionsId ? props.regionsId : []);
const selector = ref<any>(null);
const dialog = ref(true);
const value = ref<any[]>(props.modelValue ? props.modelValue : []);
const search = ref("");

onMounted(() => {
  nextTick(() => {
    init();
  })
});

watch(search, debounce(() => {
  selector.value.filter()
}, 500));

const onFilter = (a: any, obj: any, node: any) => {
  return node.data.name.toLowerCase().includes(search.value.toLowerCase());
}

const handleCheckChange = (
    data: any,
    checked: boolean,
    indeterminate: boolean
) => {
  const model = Object.assign({}, data);
  if (!model.geo_region_id) {
    // skip groups
    if (checked) {
      selectedRegionsId.value.push(model.id)
    } else {
      selectedRegionsId.value.splice(selectedRegionsId.value.indexOf(model.id, 1));
    }
    makeRegionsUnique();
    return;
  }
  if (checked) {
    value.value.push(model);
  } else {
    value.value = value.value.filter((i: any) => i.id !== model.id);
  }

  makeCitiesUnique();
}

const init = () => {
  if (value.value) {
    value.value.forEach((city: any) => {
      select(city.id)
    })
  }

  if (selectedRegionsId.value) {
    selectedRegionsId.value.forEach((regionId: number) => {
      const key = `region-${regionId}`;
      const node = selector.value.getNode(key);
      if (!node.checked) selector.value.setChecked(key, true, true);
    })
  }

  selector.value.getCheckedNodes().forEach((node: any) => {
    if (node.key.includes('region') && !selectedRegionsId.value.includes(node.id)) {
      const key = `region-${node.id}`;
      selector.value.setChecked(key, false, true)
    }
  })

  makeCitiesUnique();
}

const reset = () => {
  selector.value.setCheckedKeys([]);
  selector.value.setCheckedNodes([]);
}

const makeRegionsUnique = () => {
  selectedRegionsId.value = selectedRegionsId.value.filter((i: number, k: number, self: number[]) => self.indexOf(i) === k);
}

const makeCitiesUnique = () => {
  value.value = value.value.filter((i: any, k: number, self: any[]) => self.indexOf(i) === k);
}

const submit = () => {
  emit('regionsUpdate', selectedRegionsId.value)
  emit('update:modelValue', value.value)
  emit('close')
}

const onClose = () => {
  dialog.value = false;
  nextTick(() => {
    emit('close', true);
  })
}

const select = (id: number) => {
  const key = `city-${id}`;
  if (!selector.value) return;
  const node = selector.value.getNode(key);
  if (!node.checked) selector.value.setChecked(key, true, true);
}
</script>

<style scoped lang="scss">
.modal-window-title {
  text-align: center;
  @media (min-width: 992px) {
    text-align: left;
  }
}

.tree {
  margin-top: 20px;
  max-height: calc(100vh - 180px);
  /* height: 100px; */
  overflow-y: scroll;

  @media (min-width: 992px) {
    max-height: calc(100vh - 480px);
    min-height: 260px;
  }
}
</style>