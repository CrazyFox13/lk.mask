<template>
  <div>
    <div class="d-flex rating-counter">
      <el-button v-for="i in maxValue" :key="i" @click="onClick(i)" link>
        <StarFull size="21" v-if="value>=i"/>
        <StarEmpty size="21" v-else/>
      </el-button>
    </div>
    <el-checkbox class="mt-2" v-model="any" label="Не важно" @change="onChange"/>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "#imports";
import StarFull from "~/components/Common/Icons/StarFull.vue";
import StarEmpty from "~/components/Common/Icons/StarEmpty.vue";

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
const value = ref<number | null>(null);
const any = ref<boolean>(props.modelValue === null);
const maxValue = 5;

const onClick = (i: number) => {
  value.value = i;
  any.value = false;
}

const onChange = () => {
  value.value = null;
  any.value = true;
}

watch(value, () => {
  emit('update:modelValue', value.value)
});

watch(props, () => {
  value.value = props.modelValue;
  any.value = props.modelValue === null;
}, {deep: true})

</script>

<style scoped lang="scss">
.el-button {
  padding: 0;
}

.el-button + .el-button {
  margin-left: 2px;
}
</style>