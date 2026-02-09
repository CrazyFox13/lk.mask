<template>
  <el-card>
    <div class="avatar-container">
      <AvatarUploader class="avatar-uploader" @click="avatarDialog=true"/>
      <div>
        <div class="flex rating-section align-items-center">
          <RatingStars size="12" class="mr-3" :rating="user.rating"/>
          <LikesCount class="mr-2" :likes="user.approved_recommendations_count"/>
          <DislikesCount :dislikes="user.active_reports_count"/>
        </div>
        <div class="text-h3">{{ user.name }} {{ user.surname }}</div>
        <div class="flex align-items-center text-subtitle">
          <SvgIcon name="clock" class="mr-2"/>
          Зарегистрирован {{ ruMoment(user.created_at).fromNow() }}
        </div>
      </div>
    </div>

    <div v-if="!user.company || !user.company.approved_at" class="no-company mt-30">
      <div class="flex align-items-center">
        <SvgIcon name="alert-red" class="mr-2"/>
        <h5>
          У вас нет подтвержденной компании
        </h5>
      </div>
      <p>Заполните информацию о своей компании, чтобы получить дополнительные возможности на портале</p>
    </div>

    <AvatarUploaderDialog
        v-if="avatarDialog"
        @close="avatarDialog=false"
    />
  </el-card>
</template>

<script setup lang="ts">
import AvatarUploader from "~/components/Profile/Index/AvatarUploader.vue";
import RatingStars from "~/components/Common/RatingStars.vue";
import LikesCount from "~/components/Common/LikesCount.vue";
import DislikesCount from "~/components/Common/DislikesCount.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import SvgIcon from "~/components/SvgIcon.vue";
import moment from "moment";
import AvatarUploaderDialog from "~/components/Profile/Index/AvatarUploaderDialog.vue";
import {ruMoment} from "#imports";

const {user} = storeToRefs(useAuthStore());
const avatarDialog = ref(false);
</script>

<style scoped lang="scss">
.avatar-container {
  display: flex;
  flex-flow: column;
  align-items: center;
  text-align: center;

  .avatar-uploader {
    margin-bottom: 25px;
  }

  .rating-section {
    justify-content: center;
  }

  @media (min-width: 992px) {
    flex-flow: row;
    align-items: center;
    text-align: left;

    .avatar-uploader {
      margin-bottom: 0;
      margin-right: 20px;
    }

    .rating-section {
      justify-content: flex-start;
    }
  }
}

.text-h3 {
  margin-top: 11px;
  margin-bottom: 8px;
}

.no-company {
  border: 1px solid #F92609;
  border-radius: 10px;
  padding: 20px;

  h5, p {
    margin: 0;
  }

  p {
    margin-top: 15px;
  }
}
</style>