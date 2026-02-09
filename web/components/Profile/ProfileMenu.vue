<template>
  <div class="menu-list">
    <div
        class="menu-item"
        v-for="(item,key) in items"
        :key="key"
        v-bind:class="{'active':isActive(item)}"
    >
      <nuxt-link class="el-link" :to="item.url" @click="onClick(item)">
        <SvgIcon width="24px" height="24px" :name="item.icon" class="mr-2">
          <template #badge>
            <BadgeLabel :value="item.badge"/>
          </template>
        </SvgIcon>
        {{ item.label }}
      </nuxt-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import {type MenuItem} from "~/pages/profile.vue";
import {watch} from "#imports";
import BadgeLabel from "~/components/Common/BadgeLabel.vue";

const props = defineProps(['items', 'currentTab']);
const tab = ref(props.currentTab);
watch(props, () => {
  tab.value = props.currentTab;
}, {deep: true})
const emit = defineEmits(['go']);
const {isMobile} = storeToRefs(useDeviceStore());
const isActive = (item: MenuItem) => {
  return !isMobile.value && tab.value?.key === item.key;
}

const onClick = (item: any) => {
  if (item.url) {
    emit('go');
  } else if (item.handler) {
    item.handler();
  }
}

</script>

<style scoped lang="scss">
.menu-list {
  .menu-item {
    padding: 16px 0;
    border-bottom: 1px solid #E8EBF1;

    &:first-of-type {
      border-top: 1px solid #E8EBF1;
    }

    &.active {
      color: #2D6679;
    }
  }
}
</style>