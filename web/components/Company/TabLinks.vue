<template>
  <client-only>
    <el-dropdown class="company-linker" v-if="isMobile">
      <el-button class="tab-link">
        {{ activeLabel }}
        <SvgIcon name="chevron-down"/>
      </el-button>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item
              v-for="item in items"
              :key="item.key"

          >
            <el-link :href="item.href">{{ item.label }}</el-link>
          </el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>

    <div class="flex" v-else>
      <nuxt-link
          v-for="item in items" :key="item.key"
          class="el-link tab-link"
          v-bind:class="{'active':tab===item.key}"
          :to="item.href"
      >
        {{ item.label }}
      </nuxt-link>
    </div>
  </client-only>
</template>

<script setup lang="ts">
import {computed} from "#imports";
import {storeToRefs} from "pinia";
import {useDeviceStore} from "~/stores/device";
import SvgIcon from "~/components/SvgIcon.vue";

const props = defineProps(['companyId']);

const route = useRoute();
const {isMobile} = storeToRefs(useDeviceStore());
const tab = computed(() => {
  switch (true) {
    case route.path.includes('passport'):
      return "passport";
    case route.path.includes('orders'):
      return "orders";
    case route.path.includes('reviews'):
      return "reviews";
    case route.path.includes('portfolio'):
      return "portfolio";
    case route.path.includes('employees'):
      return "employees";
    case route.path.includes('history'):
      return "history";
    default:
      return "info";
  }
});

const items = [
  {
    label: 'Информация',
    href: `/companies/${props.companyId}`,
    key: 'info'
  },
    {
    label: 'Паспорт компании',
    href: `/companies/${props.companyId}/passport`,
    key: 'passport'
  },
  {
    label: 'Заявки',
    href: `/companies/${props.companyId}/orders`,
    key: 'orders'
  },
  {
    label: 'Отзывы',
    href: `/companies/${props.companyId}/reviews`,
    key: 'reviews'
  },
  {
    label: 'Портфолио',
    href: `/companies/${props.companyId}/portfolio`,
    key: 'portfolio'
  },
  {
    label: 'Сотрудники',
    href: `/companies/${props.companyId}/employees`,
    key: 'employees'
  },
  {
    label: 'История изменений',
    href: `/companies/${props.companyId}/history`,
    key: 'history'
  },
];

const activeLabel = computed(() => {
  const item = items.find(i => i.key === tab.value);
  return item && item.label;
})
</script>

<style lang="scss">
.company-linker {
  width: 100%;

  .el-button {
    width: 100%;

    span {
      justify-content: space-between;
      width: 100%;

      span {
        width: auto;
      }
    }
  }
}
</style>