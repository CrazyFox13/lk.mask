<template>
  <div>
    <div>
      <Preloader v-if="loading"/>
      <el-card>
        <div class="list-item" v-for="log in userLogs" :key="log.id">
          <div class="flex align-items-center">
            <SvgIcon name="form-check" class="mr-2 mb-auto"/>
            <p v-html=" log.text "></p>
          </div>
          <div class="text-gray text-sub-subtitle flex align-items-center">
            <SvgIcon name="clock" class="mr-2"/>
            {{ ruMoment(log.created_at).fromNow() }}
          </div>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import {apiFetch} from "~/composables/apiFetch";
import {nextTick, useAsyncData, watch} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";
import {ruMoment} from "#imports";
import Preloader from "~/components/Preloader.vue";

const props = defineProps(['company']);

const route = useRoute();
const companyId = Number(route.params.id);
const loading = ref(false);
const totalCount = ref(0);
const pagesCount = ref(1);
const page = ref(1);
const userLogs = ref<any[]>([]);
const TAKE = 20;

const userId = props.company?.boss.id;

const getLogs = async () => {
  loading.value = true;
  const data = await apiFetch(`customers/${userId}/logs?page=${page.value}&take=${TAKE}`, {}, true);
  userLogs.value = data.userLogs;
  totalCount.value = data.totalCount;
  pagesCount.value = data.pagesCount;
  await nextTick(() => {
    loading.value = false;
  })
};

useAsyncData(() => getLogs());

watch(page, () => {
  getLogs();
})
</script>

<style scoped lang="scss">
.list-item {
  padding: 10px 0;
  border-bottom: 1px solid #E8EBF1;
  display: flex;
  flex-flow: column;
  row-gap: 8px;


  p, h5 {
    margin: 0;
  }

  &:last-of-type{
    border-bottom: none;
  }

  @media (min-width: 992px) {
    padding: 20px 0;
  }
}
</style>