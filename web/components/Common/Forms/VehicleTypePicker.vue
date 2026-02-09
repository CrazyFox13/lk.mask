<template>
  <el-tree
      v-bind:class="{'limit-height':limitHeight}"
      class="tree"
      :data="items"
      show-checkbox
      node-key="key"
      :default-expand-all="!collapsed"
      :props="defaultProps"
      ref="selector"
      @check-change="handleCheckChange"
  />
</template>

<script setup lang="ts">
import {apiFetch} from "~/composables/apiFetch";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {onMounted, watch, ref} from "#imports";

const {isMobile} = storeToRefs(useDeviceStore())
const dialog = ref(false);

const defaultProps = {
  children: 'types',
  label: 'title',
}

const emit = defineEmits(['update:modelValue', 'updated']);
const props = defineProps(['modelValue', 'limitHeight', 'collapsed', 'groups']);

const value = ref<number[]>(props.modelValue ? props.modelValue : []);
const selectedTypes = ref<any[]>([]);
const selector = ref<any>(null);
const vehicleGroups = ref<any>(props.groups);
if (!vehicleGroups.value) {
  const data = await apiFetch('vehicle-groups');
  vehicleGroups.value = data.vehicleGroups;
}

const items = vehicleGroups.value.map((vg: any) => {
  vg.key = `group-${vg.id}`;
  vg.types = vg.types.map((vt: any) => {
    vt.key = `type-${vt.id}`
    return vt;
  })
  return vg;
});

onMounted(() => {
  if (value.value.length > 0) {
    const selected = value.value.map((i: any) => `type-${i}`)
    selector.value.setCheckedKeys(selected);
  }
})

const reset = () => {
  selector.value.setCheckedKeys([]);
  selector.value.setCheckedNodes([]);
}

const handleCheckChange = (
    data: any,
    checked: boolean,
    indeterminate: boolean
) => {
  const model = Object.assign({}, data);
  if (!model.vehicle_group_id && model.types) {
    // снимаем у всей группы?
    return;
  }

  const idx = selectedTypes.value.findIndex(t => t.id === model.id);
  if (checked) {
    selectedTypes.value.push(model);
  } else {
    selectedTypes.value.splice(idx, 1);
  }
}

watch(selectedTypes.value, (types) => {
  value.value = types.map((t: any) => t.id);
  emit('update:modelValue', value.value);
  emit('updated', selectedTypes.value);
}, {deep: true});

defineExpose({reset});

</script>

<style scoped lang="scss">
h4 {
  text-align: center;
  @media (min-width: 992px) {
    text-align: left;
  }
}

.el-select {
  width: 100%;
}

.tree {
  margin-top: 20px;
}

.btn-submit {
  width: 100%;
  margin-top: 20px;
}

.limit-height {
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