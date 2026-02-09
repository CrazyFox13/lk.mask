<template>
  <div>
    <el-card>
      <div class="flex mb-10">
        <div class="company-container ">
          <nuxt-link v-if="company" :to="`/companies/${company.id}`" class="text-h4" v-bind:class="{'blur':!user}">
            <SvgIcon name="verification" v-if="company.verified"/>
            {{ company.full_title }}
          </nuxt-link>
          <div v-else>
            <div  class="text-h4" v-bind:class="{'blur':!user}">
              {{ `${author.name} ${author.surname}` }}
            </div>
            <p class="text-sub-subtitle flex align-items-center">
              <SvgIcon name="clock" class="mr-2"/>
              Зарегистрирован {{ moment().diff(moment(user.created_at), 'days') }} дней назад
            </p>
          </div>

          <p v-bind:class="{'blur':!user}" v-if="company">
            {{ author ? `${author.name} ${author.surname}` : 'Пользователь удалён' }}</p>
          <div class="flex mb-10" v-if="company">
            <RatingStars class="mr-3" :rating="company.rating"/>
            <LikesCount class="mr-2" :likes="company.approved_recommendations_count"/>
            <DislikesCount :dislikes="company.active_reports_count"/>
          </div>
        </div>
        <el-image
            fit="cover"
            class="avatar"
            :src="author?.avatar?author.avatar:boss?.avatar?boss.avatar:DefaultAvatar"
            alt="avatar"
        />
      </div>
      <p class="text-sub-subtitle flex align-items-center" v-if="company && company.approved_at">
        <SvgIcon name="clock" class="mr-2"/>
        Зарегистрирован {{ moment().diff(moment(company.approved_at), 'days') }} дней назад
      </p>
      <div class="buttons hidden-sm-and-down">
        <el-button
            v-if="showOfferButton"
            :type="offerSent?'info':'primary'"
            class="mr-2 py-2"
            size="small"
            @click="onOfferCreate()"
            :disabled="offerSent"
        >
          <SvgIcon name="message-dollar" class="mr-2"/>
          Откликнуться
        </el-button>
        <el-link
            :data-href="`tel:+${author.phone}`"
            v-if="author?.phone"
            v-bind:class="{'el-button--secondary':!phone,'el-button--info':phone}"
            @click.prevent="onContactClick"
            class="el-button el-button--small py-2"
            target="_blank"
        >
          <SvgIcon v-if="!phone" name="phone" class="mr-2"/>
          {{ phone ? formatPhone(author.phone) : 'Позвонить' }}
        </el-link>
      </div>
    </el-card>
  </div>
</template>

<script lang="ts" setup>
import RatingStars from "~/components/Common/RatingStars.vue";
import LikesCount from "~/components/Common/LikesCount.vue";
import DislikesCount from "~/components/Common/DislikesCount.vue";
import SvgIcon from "~/components/SvgIcon.vue";
import {computed, emitter, formatPhone, onBeforeRouteLeave, ref, useRouter} from "#imports";
import moment from "moment";
import {useAuthStore} from "~/stores/user";
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import {storeToRefs} from "pinia";

const props = defineProps(['order']);
const phone = ref(false);

const {user} = storeToRefs(useAuthStore());

const company = computed(() => {
  return props.order.company;
});

const author = computed(() => {
  return props.order.user;
});

const boss = computed(() => {
  return company.value && company.value.boss;
})

const showOfferButton = computed(() => {
  return user.value?.company_id !== company.value?.id;
})

const offerSent = computed(() => {
  return !!props.order.company_offer
});

if (author.value) {
  emitter.emit('fixed-contact', {
    title: `${company.value?.title} ${author.value.name} ${author.value.surname}`,
    hiddenValue: 'Позвонить',
    shownValue: formatPhone(author.value.phone),
    link: `tel:${author.value.phone}`,
    order_id: showOfferButton.value ? props.order.id : 0,
    offer_id: offerSent.value ? props.order.company_offer.id : 0,
  });
}

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
  window.location.href = e.target.closest('a').dataset.href;
}

onBeforeRouteLeave(() => {
  emitter.emit('fixed-contact', {
    title: ``,
    hiddenValue: '',
    shownValue: "",
    order_id: 0,
    offer_id: 0,
  });
});

const onOfferCreate = () => {
  if (!user.value) {
    router.push('/auth/sign-up');
    return
  }
  if (!user.value.allowed_to_show_contacts) {
    router.push('/profile');
    return;
  }
  emitter.emit("offer-modal")
}
</script>

<style scoped lang="scss">
.text-h4 {
  text-decoration: none;
  color: inherit;
}

.avatar {
  width: 72px;
  height: 72px;
}

.company-container {
  flex: 1;
}

.w-100 {
  width: 100%;
}

.blur {
  filter: blur(3px);
}

.buttons {
  display: flex;
  column-gap: 12px;

  .el-button {
    width: 100%;
    padding: 8px 10px;
    margin: 0;
  }
}
</style>