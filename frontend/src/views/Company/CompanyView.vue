<template>
  <div>
    <v-progress-circular
        indeterminate
        color="primary"
        v-if="!company"
    />
    <v-row v-else>
      <v-breadcrumbs
          :items="breadcrumps"
          divider="-"
      />
      <v-col cols="12">
        <v-alert color="error" class="white--text" v-if="company.deleted_at">
          Компания была удалена
          {{ moment(company.deleted_at).format("HH:mm DD.MM.YYYY") }}
        </v-alert>
        <v-btn
            v-else
            small
            :color="companyStatusLabelColor(company.moderation_status)"
            @click="moderationDialog=true">
          {{ companyStatusLabelText(company.moderation_status) }}
        </v-btn>
      </v-col>
      <v-col cols="12" md="6">
        <div class="text-subtitle-1">Профиль компании</div>
        <CompanyForm ref="form" v-model="company"/>
      </v-col>
      <v-col cols="12" md="6">
        <div class="text-subtitle-1">Информация о компании</div>
        <CompanyReservedNumber v-if="company.id" :company="company"/>
        <CompanyInfo v-if="company.id" @updated="getCompany()" :company="company"/>
        <div class="text-subtitle-1">Награды</div>
        <CompanyAwards :company_id="company_id"/>
        <div class="text-subtitle-1">Претензии</div>
        <CompanyReports :company_id="company_id"/>
        <div class="text-subtitle-1">Рекомендации</div>
        <CompanyRecommendations :company_id="company_id"/>
        <div class="text-subtitle-1">Портфолио</div>
        <PhotoGroups :company="company"/>
        <CompanyUsers :company_id="company_id"/>
        <div class="text-subtitle-1">Логи</div>
        <CompanyLogs v-if="company.boss" :user_id="company.boss.id"/>
      </v-col>
      <v-col cols="12">
        <div class="text-subtitle-1">Заявки компании</div>
        <CompanyOrders :company_id="company_id"/>
      </v-col>
      <v-dialog v-model="moderationDialog" max-width="400">
        <ModerationDialog @errors="onErrors" v-model="company" @close="moderationDialog=false"/>
      </v-dialog>
    </v-row>
  </div>
</template>

<script>
import CompanyReports from "@/components/Company/Reports";
import CompanyRecommendations from "@/components/Company/Recommendations";
import CompanyForm from "@/components/Company/CompanyForm";
import CompanyInfo from "@/components/Company/CompanyInfo";
import ModerationDialog from "@/components/Company/ModerationDialog";
import PhotoGroups from "@/components/Company/PhotoGroups";
import CompanyOrders from "@/components/Company/CompanyOrders";
import CompanyUsers from "@/views/Company/CompanyUsers";
import CompanyReservedNumber from "@/components/Company/CompanyReservedNumber";
import CompanyAwards from "@/views/Company/CompanyAwards";
import moment from "moment";
import CompanyLogs from "@/components/Company/CompanyLogs.vue";

export default {
  name: "CompanyView",
  components: {
    CompanyLogs,
    CompanyAwards,
    CompanyReservedNumber,
    CompanyUsers,
    CompanyOrders,
    PhotoGroups, ModerationDialog, CompanyInfo, CompanyForm, CompanyRecommendations, CompanyReports
  },
  data() {
    return {
      company_id: Number(this.$route.params.id),
      company: undefined,
      moderationDialog: false,
      moment: moment,
    }
  },
  created() {
    this.getCompany()
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Компании',
          disabled: false,
          href: '/admin/companies',
        },
        {
          text: `${this.company?.title}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getCompany() {
      this.company = undefined;
      this.$http.get(`companies/${this.company_id}`).then(r => {
        this.company = r.body.company;
      }).catch(() => {
        this.$router.replace('/companies')
      })
    },
    onErrors(err) {
      this.$refs.form.errors = err;
    }
  }
}
</script>

<style scoped>

</style>