<template>
  <div>
    <slot name="trigger">
      <TextInput
          placeholder="Выберите вид техники"
          @focusin="onInputActive"
          v-model="readOnlyValue"
          :white="white"
      >
        <template #suffix>
          <SvgIcon name="chevron-down"/>
        </template>
      </TextInput>
    </slot>

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
            <el-button class="close-btn text-black hidden-md-and-up" circle text @click="close">
              <SvgIcon name="close"/>
            </el-button>
            <div class="text-black modal-window-title m-0">Вид техники</div>
            <el-button link size="small" class="text-gray reset-btn hidden-md-and-up" @click="reset()">
              Сбросить
            </el-button>
            <el-button class="close-btn text-black hidden-sm-and-down" circle text @click="close">
              <SvgIcon name="close"/>
            </el-button>
          </div>
        </template>
        <VehicleTypePicker
            v-if="dialog && vehicleGroups.length"
            :groups="vehicleGroups"
            :limit-height="true"
            ref="selector"
            @updated="onUpdated"
            v-model="value"/>

        <div class="flex">
          <el-button class="btn-submit" type="primary" @click="submit()">Применить</el-button>
          <el-button class="btn-submit hidden-sm-and-down" type="primary" plain @click="reset()">Сбросить</el-button>
        </div>
      </el-dialog>
    </client-only>
  </div>
</template>

<script setup lang="ts">
import TextInput from "~/components/Common/Forms/TextInput.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import VehicleTypePicker from "~/components/Common/Forms/VehicleTypePicker.vue";
import {apiFetch} from "~/composables/apiFetch";
import {ref, watch} from "#imports";

const {isMobile} = storeToRefs(useDeviceStore())
const dialog = ref(false);


const defaultProps = {
  children: 'types',
  label: 'title',
}

const emit = defineEmits(['update:modelValue', 'vehicleString']);
const props = defineProps(['modelValue', 'white']);

const readOnlyValue = ref('');
const value = ref<number[]>(props.modelValue ? props.modelValue : []);

const selector = ref<any>(null);

const onInputActive = () => {
  dialog.value = true;
}
defineExpose({onInputActive})

const handleClose = () => {
  dialog.value = false;
}

const reset = () => {
  selector.value.reset();
}

const submit = () => {
  emit('update:modelValue', value.value);
  dialog.value = false;
}

const onUpdated = (types: any) => {
  readOnlyValue.value = types.map((t: any) => t.title).join(', ');
  emit('vehicleString', readOnlyValue.value);
}

const {vehicleGroups} = await apiFetch('vehicle-groups');
let selectedTypes = ref<any[]>([]);
const setTypes = () => {
  if (value.value) {
    for (let group of vehicleGroups) {
      for (let type of group.types) {
        if (value.value.includes(type.id)) {
          selectedTypes.value.push(type);
        }
      }
    }
    onUpdated(selectedTypes.value)
  }
}
setTypes();

watch(props, () => {
  selectedTypes.value = [];
  value.value = props.modelValue;
  setTypes();
}, {deep: true})

</script>

<style scoped lang="scss">
.modal-window-title {
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
</style>