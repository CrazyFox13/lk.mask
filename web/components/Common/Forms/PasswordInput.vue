<template>
  <TextInput :type="type" :placeholder="placeholder">
    <template #suffix>
      <el-button circle link @click="toggleType()">
        <SvgIcon name="eye"/>
      </el-button>
    </template>
  </TextInput>
</template>

<script setup>
import SvgIcon from "../../SvgIcon";
import TextInput from "~/components/Common/Forms/TextInput";

const props = defineProps(['placeholder']);

const type = ref('password');

const toggleType = () => {
  type.value = type.value === 'password' ? 'text' : 'password';
}

</script>

<style scoped>

</style>