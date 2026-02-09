<template>
  <div v-bind:class="{'input-error':!!error}">
    <client-only>
      <el-autocomplete
          :placeholder="placeholder"
          v-model="value"
          :fetch-suggestions="querySearch"
          @select="handleSelect"
      >
        <template #default="{ item }">
          <div class="value">{{ item.name }}</div>
        </template>
      </el-autocomplete>
    </client-only>
    <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import {apiFetch, watch} from "#imports";


const props = defineProps(['modelValue', 'placeholder', 'error', 'default']);
const value = ref<string>(props.default?.name);
const emits = defineEmits(['update:modelValue']);

const querySearch = async (queryString: string, cb: any) => {
  const {cities} = await apiFetch(`geo-cities?search=${queryString ? queryString : ''}`) // call callback function to return suggestions
  cb(cities)
}

const handleSelect = (item: any) => {
  value.value = item.name;
  emits('update:modelValue', item.id);
}

</script>

<style lang="scss">
.el-autocomplete {
  width: 100%;
}
</style>