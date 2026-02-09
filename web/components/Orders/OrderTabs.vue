<template>
  <div class="tabs">
    <div class="item" v-bind:class="{'active':tab==='all'}" @click="onClick('all')">Все</div>
    <div class="item" v-bind:class="{'active':tab==='vehicle'}" @click="onClick('vehicle')">
      {{company && company.vehicle_types_id && company.vehicle_types_id.length > 0 ? "На вашу технику": "Выбрать технику"}}
    </div>
    <nuxt-link class="item" to="/orders/map" >
      Рядом с вами
    </nuxt-link>
  </div>
</template>

<script setup lang="ts">
import {ref, watch} from "#imports";
import {type ICompany, useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";

type IState = "all" | "vehicle" | "map"
const {user} = storeToRefs(useAuthStore());
const company = ref<ICompany | undefined>(user.value && user.value.company ? user.value.company : undefined)

const emit = defineEmits(['changed']);
const props = defineProps({
  state: {
    type: String as () => IState,
    default: "all"
  },
});

const tab = ref<IState>(props.state);

const onClick = (s: IState) => {
  tab.value = s;
  emit('changed', s);
}

watch(() => props.state, (newV: IState) => {
  tab.value = newV
})
</script>

<style scoped lang="scss">
.tabs {
  border-radius: 10px;
  background-color: #F3F5F9;
  padding: 4px 4px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  column-gap: 4px;
  margin-bottom: 16px;

  .item {
    color: #72767B;
    text-align: center;
    padding: 4px 0;
    cursor: pointer;

    &.active {
      background: #FFFFFF;
      color: #000000;
      border-radius: 6px;
    }
  }
}
</style>