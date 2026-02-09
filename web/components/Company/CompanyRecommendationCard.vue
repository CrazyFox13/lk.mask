<template>
  <el-card>
    <div class="flex mb-20">
      <el-avatar
          :alt="recommendation.company.title"
          :src="recommendation.author.avatar?recommendation.author.avatar:DefaultAvatar"
      />
      <div class="flex flex-column">
        <div class="text-h4 title-with-icon">
          <SvgIcon name="verification" v-if="recommendation.company.verified"/>
          {{ recommendation.company.title }}
        </div>
        <p>{{ recommendation.author.name }} {{ recommendation.author.surname }}</p>
      </div>
    </div>
    <p v-html="short ? truncate(recommendation.text, maxShortLength) : recommendation.text"></p>
    <el-link type="primary" class="mt-10" v-if="isLong" @click="short=!short">
      {{short?'Читать далее':'Свернуть'}}
    </el-link>
    <p class="text-subtitle text-gray mt-20">{{ ruMoment(recommendation.created_at).fromNow() }}</p>
  </el-card>
</template>

<script setup lang="ts">
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import {truncate,ruMoment} from "#imports";
import SvgIcon from "~/components/SvgIcon.vue";

const props = defineProps(['recommendation']);
const maxShortLength = 300;
const isLong = props.recommendation.text.length > maxShortLength;
const short = ref(true);
</script>

<style scoped lang="scss">
.el-avatar {
  margin-right: 16px;
}

.text-h4, p {
  margin: 0;
}
</style>