<template>
  <el-card>
    <div>
      <div class="flex flex-column align-items-center p-rel">
        <nuxt-link :to="`/companies/${company.id}/passport`">
          <el-avatar :src="company.boss.avatar?company.boss.avatar:DefaultAvatar" alt="avatar"/>
        </nuxt-link>
        <p class="text-gray text-subtitle mt-0 mb-0">№{{ company.reg_number }}</p>
        <nuxt-link :to="`/companies/${company.id}/passport`">
          <div class="text-h4 title-with-icon">
            <SvgIcon name="verification" v-if="company.verified"/>
            {{ company.title }}
          </div>
        </nuxt-link>
        <div class="flex mt-10">
          <RatingStars size="14" class="mr-4" :rating="company.rating"/>
          <LikesCount class="mr-2" :likes="company.approved_recommendations_count"/>
          <DislikesCount :dislikes="company.active_reports_count"/>
        </div>
        <div class="buttons-bar-abs">
          <el-tooltip
              class="box-item"
              effect="dark"
              content="Добавить в избранное"
              placement="top-start"
          >
            <FavoriteButton :company="company"/>
          </el-tooltip>
          <el-tooltip
              class="box-item"
              effect="dark"
              content="Поделиться"
              placement="top-start"
          >
            <SharePanel :link="`https://astt.su/companies/${company.id}`" :text="shareText">
              <template #default>
                <el-button>
                  <SvgIcon class="text-gray" name="share"/>
                </el-button>
              </template>
            </SharePanel>
          </el-tooltip>
        </div>
      </div>

      <div class="flex flex-column mt-20">
        <div class="flex align-items-center mb-10" v-if="company.approved_at">
          <SvgIcon name="clock" class="mr-2"/>
          Зарегистрирован {{ moment(company.approved_at).format("DD.MM.YYYY") }}
        </div>
        <div class="flex align-items-center mb-10" v-if="company.city">
          <SvgIcon name="location" class="mr-2"/>
          {{ company.city.name }}
        </div>
        <div class="flex align-items-center">
          <SvgIcon name="order" class="mr-2"/>
          Опубликовано {{ company.active_orders_count }} заявок
        </div>
      </div>

      <div class="flex flex-wrap mt-20 buttons-bar">
        <el-button
            class="hidden-sm-and-down"
            v-if="company.boss?.phone"
            :type="phone?'info':'primary'"
            @click.prevent="onContactClick"
            :data-href="`tel:+${company.boss.phone}`"
        >
          {{ phone ? formatPhone(company.boss.phone) : 'Показать телефон' }}
        </el-button>
        <el-button type="info" @click="emit('recommendation')">
          <LikeIcon size="15" class="mr-2"/>
          Рекомендация
        </el-button>
        <el-button type="info" @click="emit('report')">
          <DislikeIcon size="15" class="mr-2"/>
          Претензия
        </el-button>
      </div>
    </div>
  </el-card>
</template>

<script lang="ts" setup>
import SvgIcon from "~/components/SvgIcon.vue";
import moment from "moment";
import RatingStars from "~/components/Common/RatingStars.vue";
import LikesCount from "~/components/Common/LikesCount.vue";
import DislikesCount from "~/components/Common/DislikesCount.vue";
import LikeIcon from "~/components/Common/Icons/LikeIcon.vue";
import DislikeIcon from "~/components/Common/Icons/DislikeIcon.vue";
import FavoriteButton from "~/components/Company/FavoriteButton.vue";
import {computed, emitter, formatPhone, formatPrice, onBeforeRouteLeave, ref, useRouter} from "#imports";
import CreateRecommendationDialog from "~/components/Company/CreateRecommendationDialog.vue";
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import SharePanel from "~/components/Common/SharePanel.vue";
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";

const props = defineProps(['company']);
const emit = defineEmits(['recommendation', 'report']);
const {user} = storeToRefs(useAuthStore());
const phone = ref(false);

emitter.emit('fixed-contact', {
  title: props.company?.title,
  hiddenValue: 'Позвонить',
  shownValue: formatPhone(props.company.boss.phone),
  link: `tel:${props.company.boss.phone}`
});

onBeforeRouteLeave(() => {
  emitter.emit('fixed-contact', {
    title: ``,
    hiddenValue: '',
    shownValue: ""
  });
})

const shareText = computed(() => {
  const company = props.company;
  return `🔥 Посмотри ${company.title}.
👉🏻 https://astt.su/companies/${company.id} 👈🏻`
});

const router = useRouter();
const onContactClick = (e: any) => {
  if (!user.value) {
    try {
      (window as any).ym(65983300, 'reachGoal', 'show_phone_guest')
    } catch (e) {
      console.log("ym", 'show_phone_guest', e)
    }
    router.push('/auth/sign-up');
    return;
  }
  if (!user.value.allowed_to_show_contacts) {
    router.push('/profile');
    return;
  }
  if (!phone.value) {
    phone.value = true;
    try {
      (window as any).ym(65983300, 'reachGoal', 'show_phone')
    } catch (e) {
      console.log('ym', 'show_phone', e);
    }
    return;
  }
  try {
    (window as any).ym(65983300, 'reachGoal', 'click_phone')
  } catch (e) {
    console.log("ym", 'click_phone', e)
  }
  window.location.href = e.target.closest('button').dataset.href;
}
</script>

<style scoped lang="scss">
.el-card {
  .el-avatar {
    width: 80px;
    height: 80px;
    margin-bottom: 8px;
  }

  .p-rel {
    position: relative;

    .text-h4 {
      margin-top: 8px;
      width: auto;
    }
  }

  .buttons-bar {
    column-gap: 10px;

    & > .el-button {
      width: calc(50% - 5px);
      margin-left: 0;
      margin-right: 0;
    }
  }

  @media (min-width: 992px) {
    .buttons-bar {
      & > .el-button:first-of-type {
        width: 100%;
      }

      & > .el-button:nth-of-type(2) {
        margin-left: 0;
      }

      & > .el-button:not(:first-of-type) {
        flex: 1;
        margin-top: 10px;
      }
    }

    position: sticky;
    top: 20px;
    margin-top: 20px;
  }
}

.buttons-bar-abs {
  position: absolute;
  display: flex;
  flex-flow: column;
  top: 0;
  right: 0;
  row-gap: 10px;

  .el-button {
    width: 24px;
    height: 24px;
    padding: 0;
    margin: 0;
  }
}

</style>