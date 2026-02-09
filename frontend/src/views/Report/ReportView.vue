<template>
  <div>
    <v-progress-circular indeterminate color="primary" v-if="!report"/>
    <div v-else>
      <v-breadcrumbs :items="breadcrumps" divider="-"/>
      <v-alert color="error" class="white--text" v-if="report.deleted_at">
        Претензия была удалена
        {{ moment(report.deleted_at).format("HH:mm DD.MM.YYYY") }}
      </v-alert>
      <v-btn v-else small :color="reportStatusLabelColor(report.status)" @click="moderationDialog=true">
        {{ reportStatusLabelText(report.status) }}
      </v-btn>
      <v-row class="mt-3">
        <v-col cols="12" md="6">
          <div class="text-subtitle-1">Информация о жалобе</div>
          <ReportInfo :report="report"/>
          <RefereeConclusion class="mt-3" :report="report"/>
        </v-col>
        <v-col cols="12" md="6" v-if="report.chat">
          <div class="text-subtitle-1">Чат</div>
          <ReportChat :chat="report.chat"/>
        </v-col>
      </v-row>
      <v-dialog v-model="moderationDialog" max-width="400">
        <ReportModeration v-model="report" @close="moderationDialog = false;"/>
      </v-dialog>
    </div>
  </div>
</template>

<script>
import ReportInfo from "@/components/Report/ReportInfo";
import ReportChat from "@/components/Report/ReportChat";
import ReportModeration from "@/components/Report/ReportModeration";
import RefereeConclusion from "@/components/Report/RefereeConclusion";
import moment from "moment";

export default {
  name: "ReportView",
  components: {RefereeConclusion, ReportModeration, ReportChat, ReportInfo},
  data() {
    return {
      report_id: Number(this.$route.params.id),
      report: undefined,
      moderationDialog: false,
      moment:moment,
    }
  },
  created() {
    this.getReport();
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Претензии',
          disabled: false,
          href: '/admin/reports',
        },
        {
          text: `#${this.report?.id}`,
          disabled: true,
          href: '#',
        },
      ]
    }
  },
  methods: {
    getReport() {
      this.$http.get(`reports/${this.report_id}`).then(r => {
        this.report = r.body.report;
      }).catch(() => {
        this.$router.replace('/reports');
      })
    }
  }
}
</script>

<style scoped>

</style>