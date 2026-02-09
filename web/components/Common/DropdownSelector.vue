<template>
  <client-only>
    <div v-if="isMobile" :class="classList">
      <el-button link class="text-black" @click="drawer=true">
        {{ selected ? selected.text : 'Не выбрано' }}
        <SvgIcon name="chevron-down" class="ml-2"/>
      </el-button>
      <el-drawer
          v-model="drawer"
          direction="btt"
          :before-close="()=>drawer=false"
          :show-close="false"
          class="dropdown-drawer"
          :size="166"
      >
        <template #header="{close}">
          <div class="modal-form-title text-black m-0">{{title}}</div>
          <el-button class="close-btn text-black" circle text @click="close">
            <SvgIcon name="close"/>
          </el-button>
        </template>
        <template #default>
          <el-radio-group class="dropdown-radio mt-10" v-model="value">
            <el-radio :label="option.key" v-for="(option,k) in options" :key="k">{{ option.text }}</el-radio>
          </el-radio-group>
        </template>
      </el-drawer>
    </div>
    <el-dropdown v-else>
      <el-button link class="el-dropdown-link text-black">
        {{ selected ? selected.text : 'Не выбрано' }}
        <SvgIcon name="chevron-down" class="ml-2"/>
      </el-button>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item v-for="(option,k) in options" :key="k" class="text-black" @click="value=option.key">
            {{ option.text }}
            <SvgIcon name="ok" class="ml-2" v-if="value===option.key"/>
          </el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>

  </client-only>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {computed, ref, watch} from "#imports";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";

export interface IDropDownSelectorOption {
  key: string
  text: string
}

const {isMobile} = storeToRefs(useDeviceStore())

const props = defineProps({
  modelValue: {
    type: String,
    default() {
      return '';
    }
  },
  options: {
    type: Array,
    default() {
      return [];
    }
  },
  title:String,
  classList: String,
});

const emit = defineEmits(['update:modelValue']);
const value = ref<string>(props.modelValue);
const drawer = ref(false);
const selected = computed(() => props.options!.find((option: any) => option.key === value.value));

watch(value, (v) => emit('update:modelValue', v))
watch(()=>props.modelValue, (v) => value.value = v)
</script>

<style lang="scss">
.dropdown-radio {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.dropdown-drawer {
  border-radius: 20px 20px 0 0;

  .el-drawer__header {
    margin-bottom: 0;
  }
}
</style>