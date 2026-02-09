<template>
  <div>
    <label class="input-title">Сроки выполнения работ</label>
    <div class="date-selector-container">
      <DatePicker
          placeholder="Дата начала"
          v-model="value.start_date"
          format="DD.MM.YYYY"
          :error="errors.start_date"
      />
      <el-select placeholder="С 00:00" v-model="hour">
        <el-option v-for="h in hours" :key="h" :value="h">
          {{ h }}
        </el-option>
      </el-select>
      <div class="helpers">
        <div
            class="helpers__item"
            v-for="(helper,i) in helpers"
            :key="i"
            @click="selectHelper(i)"
            v-bind:class="{'active':selectedHelper===i}"
        >
          {{ helper.name }}
        </div>
      </div>
      <DatePicker
          placeholder="Дата окончания"
          v-model="value.finish_date"
          format="DD.MM.YYYY"
          :error="errors.finish_date"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import DatePicker from "~/components/Common/Forms/DatePicker.vue";
import moment from "moment";
import {computed, watch} from "#imports";
import type {IWorkDuration} from "~/types/workDuration";

const props = defineProps(['modelValue', 'errors']);
const emit = defineEmits(['update:modelValue']);
const value = ref<IWorkDuration>(props.modelValue ? {
  start_date: moment(props.modelValue.start_date).toDate(),
  finish_date: moment(props.modelValue.finish_date).toDate(),
} : {
  start_date: undefined,
  finish_date: undefined
});

const hours = computed(() => {
  const data = [];
  for (let i = 0; i < 24; i++) {
    data.push(i < 10 ? `0${i}:00` : `${i}:00`);
  }
  return data;
});
const hour = ref<string | undefined>(value.value.start_date ? moment(value.value.start_date).format('HH:mm') : undefined);

watch(hour, () => {
  emitData();
})

watch(value, () => {
  emitData();
}, {deep: true});

const attachHourToDate = () => {
  if (!value.value.start_date) return;
  if (!hour.value) return;
  const h = Number(hour.value.split(":")[0])
  value.value.start_date.setHours(h);
}

const emitData = () => {
  attachHourToDate();
  const data = {
    start_date: value.value.start_date ? moment(value.value.start_date, 'DD.MM.YYYY HH:mm').utc().format("YYYY-MM-DD HH:mm") : undefined,
    finish_date: value.value.finish_date ? moment(value.value.finish_date, 'DD.MM.YYYY').utc().endOf('day').format("YYYY-MM-DD") : undefined
  };
  emit('update:modelValue', data);
}

const selectedHelper = ref<number>();
const selectHelper = (i: number) => {
  selectedHelper.value = i;
  value.value.start_date = helpers.value[i].start.toDate();
  value.value.finish_date = helpers.value[i].end.toDate();
}

const helpers = computed(() => {
  return [
    {
      name: "Сегодня",
      start: moment().startOf("day"),
      end: moment().endOf("day"),
    },
    {
      name: "Завтра",
      start: moment().add(1, 'day').startOf("day"),
      end: moment().add(1, 'day').endOf("day"),
    },
    {
      name: "Послезавтра",
      start: moment().add(2, 'day').startOf("day"),
      end: moment().add(2, 'day').endOf("day"),
    },
    {
      name: "Через неделю",
      start: moment().add(7, 'day').startOf("day"),
      end: moment().add(7, 'day').endOf("day"),
    },
    {
      name: "С завтра на 3 смены",
      start: moment().add(1, 'day').startOf("day"),
      end: moment().add(3, 'day').endOf("day"),
    },
    {
      name: "С завтра на 7 смен",
      start: moment().add(1, 'day').startOf("day"),
      end: moment().add(7, 'day').endOf("day"),
    },
  ];
})
</script>

<style scoped lang="scss">
.date-selector-container {
  display: flex;
  flex-flow: column;
  row-gap: 10px;

  @media (min-width: 992px) {
    flex-flow: revert;
    flex-wrap: wrap;
    column-gap: 15px;

    & > * {
      width: 60%;
    }

    & > .el-select {
      width: 35%;
      margin-left: auto;
    }
  }

  .helpers {
    display: flex;
    flex-wrap: wrap;
    column-gap: 8px;
    row-gap: 8px;
    width: 100%;
    margin-bottom: 8px;
    margin-top: 8px;

    &__item {
      border-radius: 50px;
      background: var(--neutral-gray-disabled-30, rgba(167, 180, 204, 0.30));
      padding: 6px 15px;
      cursor: pointer;

      &.active {
        background: var(--neutral-gray-disabled-30, rgba(167, 180, 204, 1));
      }
    }
  }
}
</style>