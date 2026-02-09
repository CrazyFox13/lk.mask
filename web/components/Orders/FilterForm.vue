<template>
  <div>
    <div class="filter-title mb-10 mt-20">Выбранный регион</div>
    <RegionPicker v-model="value.cities_id"/>

    <div class="filter-title mb-10 mt-30">Сроки заказа</div>
    <el-radio-group v-model="value.shifts" class="flex flex-column align-items-start">
      <el-radio label="one">
        На одну смену
      </el-radio>
      <el-radio label="two">
        На две смены
      </el-radio>
      <el-radio label="less_five">
        До 5 смен
      </el-radio>
      <el-radio label="more_five">
        Более 5 смен
      </el-radio>
      <el-radio label="">
        Любой
      </el-radio>
    </el-radio-group>

    <div class="filter-title mb-10 mt-30">Начало работ</div>
    <DatePicker v-model="value.date" placeholder="Дата начала"/>

    <div class="filter-title mb-10 mt-30">Расчет</div>
    <div class="flex justify-between switcher">
      <p class="filter-label">Договорная стоимость</p>
      <el-switch v-model="value.amount_by_agreement" :inactive-value="0" :active-value="1"/>
    </div>
    <div class="flex justify-between switcher">
      <p class="filter-label">Стоимость с НДС</p>
      <el-switch v-model="value.amount_with_vat" :inactive-value="0" :active-value="1"/>
    </div>
    <div class="flex justify-between switcher">
      <p class="filter-label">Наличный расчет</p>
      <el-switch v-model="value.amount_cash" :inactive-value="0" :active-value="1"/>
    </div>

    <div class="flex justify-between mt-30">
      <div class="filter-title m-0">Только от компаний</div>
      <el-switch v-model="value.with_company" :inactive-value="0" :active-value="1"/>
    </div>

    <div class="flex justify-between" v-if="!hideFavorite">
      <div class="filter-title m-0">Избранное</div>
      <el-switch v-model="value.favorite"/>
    </div>

    <el-button class="mt-30 search-btn" type="primary" @click="emit('saveFilter')" v-if="filterChanged">
      <SvgIcon name="search-heart"/>&nbsp;
      Сохранить поиск
    </el-button>

    <el-button plain type="primary" class="mt-10 search-btn" @click="emit('showFilters')" v-if="filtersExists">
      <SvgIcon name="search-heart" class="text-gray"/>&nbsp;
      Мои поиски
    </el-button>
  </div>
</template>

<script setup lang="ts">
import RegionPicker from "../Common/Forms/RegionPicker.vue";
import DatePicker from "../Common/Forms/DatePicker.vue";
import {watch, ref} from "#imports";
import {type IOrderFilter} from "~/stores/order";
import SvgIcon from "~/components/SvgIcon.vue";

const emit = defineEmits(['update:modelValue', 'saveFilter', 'showFilters']);
const props = defineProps(['modelValue', 'hideFavorite', 'filterChanged', 'filtersExists']);

const value = ref<IOrderFilter>(props.modelValue);

watch(value, () => {
  emit('update:modelValue', value.value);
}, {deep: true});

watch(props, () => {
  value.value = props.modelValue;
}, {deep: true})

</script>

<style scoped lang="scss">
.filter-label {
  margin-top: 0;
  margin-bottom: 12px;
}

.search-btn {
  margin: 0;
  width: 100%;
}
</style>