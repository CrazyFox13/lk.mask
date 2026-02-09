<template>
  <NuxtLink class="company" :to="`/companies/${company.id}`">
    <el-card class="hoverable">
      <div class="flex">
        <el-avatar
            class="company-avatar hidden-sm-and-down"
            :src="employeeData&&employeeData.avatar?employeeData.avatar:DefaultAvatar"
        />
        <div class="company-content">
          <div class="flex w-100">
            <el-avatar
                class="company-avatar hidden-md-and-up"
                :src="employeeData && employeeData.avatar?employeeData.avatar:DefaultAvatar"
                alt="CompanyAvatar"
            />

            <div class="company-title">
              <div class="text-h4 title-with-icon">
                <SvgIcon name="verification" v-if="company.verified"/>
                {{ company.title }}
              </div>
              <div class="company-rating">
                <RatingStars size="14" class="mr-4" :rating="company.rating"/>
                <LikesCount class="mr-2" :likes="company.approved_recommendations_count"/>
                <DislikesCount :dislikes="company.active_reports_count"/>
              </div>
            </div>
            <div class="button-bar hidden-md-and-up">
              <FavoriteButton :company="company"/>
            </div>
          </div>
          <p class="text-gray mt-2" v-html="truncate(company.description,200)"></p>
          <div class="company-footer">
            <div class="company-time" v-if="company.created_at">
              <SvgIcon name="clock" class="mr-2"/>
              {{ ruMoment(company.created_at).fromNow() }}
            </div>
            <div class="company-location" v-if="employeeData && employeeData.city">
              <SvgIcon name="location" class="mr-2"/>
              {{ employeeData.city.name }}
            </div>
          </div>
        </div>
        <div class="button-bar button-bar-md hidden-sm-and-down">
          <el-tooltip
              class="box-item"
              effect="dark"
              content="Добавить в избранное"
              placement="top-start"
          >
            <FavoriteButton :company="company"/>
          </el-tooltip>
        </div>
      </div>
    </el-card>
  </NuxtLink>
</template>

<script setup lang="ts">
import RatingStars from "~/components/Common/RatingStars.vue";
import LikesCount from "~/components/Common/LikesCount.vue";
import DislikesCount from "~/components/Common/DislikesCount.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import {truncate} from "#imports";
import moment from "moment";
import FavoriteButton from "~/components/Company/FavoriteButton.vue";
import {storeToRefs} from "pinia";
import {useAuthStore} from "~/stores/user";
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import {ruMoment} from "#imports";

const props = defineProps(['company', 'employee']);
const authStore = useAuthStore()
const {user} = storeToRefs(authStore);

const employeeData = props.employee ? props.employee : props.company.boss;
</script>

<style scoped lang="scss">
.company {
  margin-bottom: 20px;
  display: block;
  text-decoration: none;

  .company-content {
    display: table;
    flex-wrap: wrap;
    text-decoration: none;
    width: 100%;

    .w-100 {
      width: 100%;
    }

    .company-title {
      display: block;
      align-items: center;
      flex-wrap: wrap;
      width: 100%;

      @media (min-width: 992px) {
        display: flex;
      }

      .text-h4 {
        width: 100%;
        @media (min-width: 992px) {
          width: auto;
          margin-right: 15px;
        }
      }

      .company-rating {
        display: flex;
      }
    }
  }

  .company-footer {
    display: flex;

    .company-time {
      margin-right: 20px;
    }

    .company-time, .company-location {
      display: flex;
      align-items: center;
    }
  }
}

.company-avatar {
  width: 64px;
  min-width: 64px;
  height: 64px;
  min-height: 64px;
  margin-right: 10px;

  @media (min-width: 992px) {
    width: 120px;
    min-width: 120px;
    height: 120px;
    min-height: 120px;
    margin-right: 20px;
  }
}

.button-bar {
  display: flex;
  flex-direction: column;
  align-items: center;

  .el-button {
    width: 24px;
    height: 24px;
    border: 1px solid #F3F5F9;
    border-radius: 5px;
    margin-left: 0;
    padding: 0px;

    @media (min-width: 992px) {
      width: 28px;
      height: 28px;
    }

    &:first-of-type {
      margin-bottom: 4px;
    }
  }
}

@media (min-width: 992px) {
  .button-bar-md {
    visibility: hidden;
  }

  .el-card:hover {
    .button-bar-md {
      visibility: visible;
      margin-left: auto;
    }
  }
}

.blur {
  filter: blur(3px);
}
</style>