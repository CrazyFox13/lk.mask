<template>
  <v-app>
    <v-app-bar app color="secondary" dark clipped-left>
      <v-app-bar-nav-icon class="hidden-lg-and-up" @click.stop="drawer = !drawer"/>
      <v-spacer class="hidden-lg-and-up"/>
      <div class="d-flex align-center">
        <v-img
            alt="Vuetify Name"
            class="shrink mt-1"
            contain
            min-width="100"
            :src="require('@/assets/logo.svg')"
            width="100"
        />
      </div>

      <v-spacer></v-spacer>

      <v-btn
          v-if="user"
          to="/profile"
          icon
      >
        <v-icon>mdi-account</v-icon>
      </v-btn>
    </v-app-bar>
    <v-navigation-drawer v-if="user" app v-model="drawer" clipped>
      <v-list-item-group
          dense
          nav
          v-model="currentRoute"
          active-class="primary--text"
      >
        <template>
          <div v-for="(link,i) in links" :key="i">
            <v-list-item v-if="!link.children" link :to="link.url">
              <v-list-item-content>
                <v-badge inline v-if="link.badge && link.badge>0" :content="link.badge">
                  <v-list-item-title>{{ link.label }}</v-list-item-title>
                </v-badge>
                <v-list-item-title v-else>{{ link.label }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-group v-else>
              <template v-slot:activator>
                <v-list-item link class="pl-0">
                  <v-list-item-content>
                    <v-badge inline v-if="link.badge && link.badge>0" :content="link.badge">
                      <v-list-item-title>{{ link.label }}</v-list-item-title>
                    </v-badge>
                    <v-list-item-title v-else>
                      {{ link.label }}
                    </v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </template>
              <v-list-item
                  class="ml-3"
                  :to="child.url"
                  :key="`${i}_${j}`"
                  v-for="(child,j) in link.children"
                  link
              >
                <v-list-item-content>
                  <v-badge inline v-if="child.badge && child.badge>0" :content="child.badge">
                    <v-list-item-title>{{ child.label }}</v-list-item-title>
                  </v-badge>
                  <v-list-item-title v-else>
                    {{ child.label }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-group>
          </div>
        </template>
      </v-list-item-group>
    </v-navigation-drawer>
    <v-main>
      <router-view class="px-5 py-5" :key="$route.path" v-on:onAuth="getUser('/')"/>
    </v-main>
  </v-app>
</template>

<script>
import {mapGetters} from 'vuex';

export default {
  name: 'AppView',

  data() {
    return {
      drawer: true,
      currentRoute: 0,
    }
  },
  computed: {
    ...mapGetters(['user', 'ordersOnModerationCount', 'refereeReportsCount', 'draftClaimsCount', 'draftRecommendationsCount', 'moderationCompaniesCount']),
    links() {
      return [
        {url: "/", label: "Главная"},
        {
          label: "Пользователи", badge: this.moderationCompaniesCount,
          children: [
            {url: "/users", label: "Все пользователи"},
            {url: "/companies", label: "Компании", badge: this.moderationCompaniesCount},
          ]
        },
        {url: "/orders", label: "Заявки", badge: this.ordersOnModerationCount},
        {url: "/reports", label: "Претензии", badge: this.refereeReportsCount},
        {url: "/claims", label: "Жалобы", badge: this.draftClaimsCount},
        {url: "/recommendations", label: "Рекомендации", badge: this.draftRecommendationsCount},
        {
          label: "Уведомления",
          children: [
            {url: "/push-notifications", label: "Push-уведомления"},
            {url: "/email-notifications", label: "E-mail-уведомления"},
            {url: "/email-notification-templates", label: "E-mail-шаблоны"},
          ]
        },
        {
          label: "Отчёты",
          children: [
            {url: "/orders-report", label: "Отчёт по заявкам"},
          ]
        },
        {url: "/moderators", label: "Модераторы"},
        {
          label: "Библиотека",
          children: [
            {url: "/vehicles", label: "Техника"},
            {url: "/company-types", label: "Типы компаний"},
            {url: "/report-types", label: "Типы жалоб"},
            {url: "/payment-units", label: "Типы оплат"},
            {url: "/regions", label: "Регионы"},
            {url: "/reserved-numbers", label: "Номера компаний"},
            {url: "/awards", label: "Награды"},
          ]
        },
        {
          label: "Страницы",
          children: [
            {url: "/faq", label: "FAQ"},
            {url: "/materials", label: "Материалы"},
          ]
        },
        {
          label: "Маркетинг",
          children: [
            {url: "/advertisers", label: "Рекламодатели"},
            {url: "/adv-banners", label: "Баннеры"},
            {url: "/adv-reports", label: "Отчёты"},
          ]
        },
      ];
    }
  },
  watch: {
    '$route.path': {
      handler(path) {
        this.setActiveRoute(path);
      }
    }
  },
  created() {
    this.getUser();
    this.$store.dispatch('FETCH_BADGES');
    this.setActiveRoute(this.$route.path);
  },
  methods: {
    setActiveRoute(path) {
      this.currentRoute = this.links.findIndex(link => link.url === path);
    },
    getUser(redirect_to = null) {
      this.$store.dispatch("GET_ME").then(() => {
        if (redirect_to) {
          this.$router.push(redirect_to)
        }
      })
    }
  }
};
</script>
