<template>
  <el-card>
    <div class="text-h5">ИНН:</div>
    <p>{{ company.inn }}</p>

    <div class="text-h5">О компании:</div>
    <p>{{ company.description }}</p>

    <div v-if="company.vehicle_groups.length>0" class="text-h5">Специализация:</div>
    <el-row class="mt-20" v-if="company.vehicle_groups.length>0">
      <el-col class="company-vehicles" :span="24" :md="12" v-for="group in company.vehicle_groups" :key="group.id">
        <el-collapse accordion>
          <el-collapse-item>
            <template #title>
              <el-image class="group-logo" :src="group.logo" :alt="group.title"/>
              {{ group.title }}
            </template>
            <ul>
              <li v-for="type in group.types" :key="type.id">
                {{ type.title }}
              </li>
            </ul>
          </el-collapse-item>
        </el-collapse>
      </el-col>
    </el-row>

  </el-card>
</template>

<script setup lang="ts">

const props = defineProps(['company']);
</script>

<style lang="scss">
.company-vehicles {
  .group-logo {
    width: 40px;
    height: 40px;
    margin-right: 15px;
  }

  &:not(:first-of-type) {
    .el-collapse-item__header, .el-collapse-item__wrap, .el-collapse {
      border-top: none;
    }
  }

  @media (min-width: 992px) {
    margin-bottom: 5px;
    &:nth-last-of-type(n+1) {
      padding-right: 10px;
    }
    &:nth-last-of-type(n) {
      padding-left: 10px;
    }

    &:nth-of-type(2) {
      .el-collapse-item__header, .el-collapse-item__wrap, .el-collapse {
        border-bottom: 1px solid var(--el-collapse-border-color);
      }
    }
  }
}
</style>