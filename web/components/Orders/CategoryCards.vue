<template>
  <div>
    <div v-if="categories" class="category-container">
      <div class="category-list" v-if="randomCategories.length>0" v-dragscroll.x>
        <div
            v-for="category in randomCategories"
            :key="category.id"
            class="category-list-item"
            v-bind:style="{'background-color': category.color}"
            @click="onCategoryClick(category)"
        >
          <div class="item-name">{{ category.title }}</div>
          <img class="item-image" :src="category.image" :alt="category.title"/>
        </div>
        <OrderCardAll @click="showModal()"/>
      </div>
      <div class="group-list" v-if="randomGroups.length > 0" v-dragscroll.x>
        <OrderCardBack v-if="randomCategories.length===0" @click="reset()"/>
        <div
            v-for="group in randomGroups"
            :key="group.id"
            class="group-list-item"
            v-bind:style="{'background-color': group.color}"
            @click="onGroupClick(group)"
        >
          <div class="item-name">{{ group.title }}</div>
          <img class="item-image" :src="group.image" :alt="group.title"/>
        </div>
        <OrderCardAll v-if="randomCategories.length===0" @click="showModal()"/>
      </div>
      <div class="type-list" v-dragscroll.x>
        <OrderCardBack v-if="randomGroups.length===0" @click="reset()"/>
        <div
            v-for="type in randomTypes"
            :key="type.id"
            class="type-list-item"
            v-bind:style="{'background-color': type.color}"
            @click="onTypeClick(type)"
        >
          <div class="item-name">{{ type.title }}</div>
          <img class="item-image" :src="type.image" :alt="type.title"/>
        </div>
        <OrderCardAll v-if="randomGroups.length===0" @click="showModal()"/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "#imports";
import {type IVehicleCategory, type IVehicleGroup, type IVehicleType, useVehicleStore} from "~/stores/vehicle";
import OrderCardAll from "~/components/Orders/OrderCards/OrderCardAll.vue";
import OrderCardBack from "~/components/Orders/OrderCards/OrderCardBack.vue";
import { dragscroll } from 'vue-dragscroll'

const emit = defineEmits(["update:modelValue", "search", "showModal"])

const props = defineProps({
  modelValue: {
    type: Array as () => number[],
    required: true,
  }
});

const filter = ref(props.modelValue);
const selectedValues = ref(props.modelValue);

watch(() => props.modelValue, (newV) => {
  console.log("watch cards", newV)
  selectedValues.value = newV
  if (newV?.length === 0) {
    cards.value = {
      selectedCategoryId: undefined,
      selectedGroupId: undefined,
      selectedTypeId: undefined,
    }
  }
}, {deep: true})

const store = useVehicleStore();
const {getVehicleCategories} = store;

const cards = ref({
  selectedCategoryId: <number | undefined>undefined,
  selectedGroupId: <number | undefined>undefined,
  selectedTypeId: <number | undefined>undefined,
});

const categories = await getVehicleCategories()

const visibleCategories = computed(() => {
  if (cards.value.selectedCategoryId) {
    return [];
  }
  return categories.filter(c => c.show_in_menu)
})
const visibleGroups = computed(() => {
  if (cards.value.selectedGroupId) {
    return [];
  }

  return categories.filter(c => !cards.value.selectedCategoryId || c.id === cards.value.selectedCategoryId).map(c => c.groups).flat().filter(g => g.show_in_menu)
})
const visibleTypes = computed(() => {
  if (cards.value.selectedTypeId) {
    return [];
  }

  return categories.filter(c => !cards.value.selectedCategoryId || c.id === cards.value.selectedCategoryId).map(c => c.groups.filter(g => !cards.value.selectedGroupId || g.id === cards.value.selectedGroupId)).flat().map(c => c.types).flat().filter(t => t.show_in_menu)
})

const randomCategories = computed(() => {
  const shuffled = visibleCategories.value.sort(() => Math.random() - 0.5);
  return shuffled.slice(0, Math.min(8, shuffled.length));
});

const randomGroups = computed(() => {
  const shuffled = visibleGroups.value.sort(() => Math.random() - 0.5);
  return shuffled.slice(0, Math.min(8, shuffled.length));
});

const randomTypes = computed(() => {
  const shuffled = visibleTypes.value.sort(() => Math.random() - 0.5);
  return shuffled.slice(0, Math.min(8, shuffled.length));
});

const onCategoryClick = (category: IVehicleCategory) => {
  cards.value.selectedCategoryId = category.id;
  filter.value = category.groups.map(g => g.types.map(t => t.id)).flat()
  emit("update:modelValue", filter.value)
  emit("search")
}

const onGroupClick = (group: IVehicleGroup) => {
  cards.value.selectedCategoryId = group.vehicle_category_id;
  cards.value.selectedGroupId = group.id;
  filter.value = group.types.map(t => t.id)
  emit("update:modelValue", filter.value)
  emit("search")
}
const onTypeClick = (type: IVehicleType) => {
  const group = visibleGroups.value.find(g => g.id === type.vehicle_group_id);
  cards.value.selectedGroupId = type.vehicle_group_id;
  cards.value.selectedTypeId = type.id;
  if (group) {
    cards.value.selectedCategoryId = group.vehicle_category_id;
  }

  filter.value = [type.id]
  emit("update:modelValue", filter.value)
  emit("search")
}

const reset = () => {
  cards.value.selectedGroupId = undefined;
  cards.value.selectedTypeId = undefined;
  cards.value.selectedCategoryId = undefined;
  filter.value = [];
  emit("update:modelValue", filter.value)
  emit("search")
}

const showModal = () => {
  emit("showModal")
}

</script>

<style scoped lang="scss">
.category-container {
  display: flex;
  flex-direction: column;
  row-gap: .75em;

  .category-list {
    display: flex;
    column-gap: .75em;
    overflow-x: auto;
    scrollbar-width: none; /* Для браузеров на основе Gecko (Firefox) */
    -ms-overflow-style: none; /* Для Internet Explorer и Edge */
    &::-webkit-scrollbar {
      display: none; /* Для браузеров на основе Webkit (Chrome, Safari, Edge) */
    }

    &-item {
      width: 165px;
      min-width: 165px;
      height: 90px;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }
  }

  .group-list {
    display: flex;
    column-gap: .75em;
    overflow-x: auto;
    scrollbar-width: none; /* Для браузеров на основе Gecko (Firefox) */
    -ms-overflow-style: none; /* Для Internet Explorer и Edge */
    &::-webkit-scrollbar {
      display: none; /* Для браузеров на основе Webkit (Chrome, Safari, Edge) */
    }

    &-item {
      width: 135px;
      min-width: 135px;
      height: 90px;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }
  }

  .type-list {
    display: flex;
    column-gap: .75em;
    overflow-x: auto;
    scrollbar-width: none; /* Для браузеров на основе Gecko (Firefox) */
    -ms-overflow-style: none; /* Для Internet Explorer и Edge */
    &::-webkit-scrollbar {
      display: none; /* Для браузеров на основе Webkit (Chrome, Safari, Edge) */
    }

    &-item {
      width: 115px;
      min-width: 115px;
      height: 90px;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }
  }

  .item-name {
    font-weight: 500;
    font-size: 13px;
    line-height: 15px;
    font-family: "Roboto Medium", sans-serif;
    color: #222222;
    margin: 8px 8px;
    word-break: break-word;
  }

  .item-image {
    position: absolute;
    bottom: 0;
    right: 0;
    height: 62px;
    width: auto;
  }
}
</style>