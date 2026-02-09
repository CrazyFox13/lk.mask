<template>
  <v-card flat>
    <v-card-text>
      <v-text-field
          label="Заголовок"
          v-model="pushNotification.subject"
          :error-messages="errors.subject"
          :error-count="1"
          :error="!!errors.subject"
      />
      <v-textarea
          label="Текст"
          v-model="pushNotification.text"
          :error-messages="errors.text"
          :error-count="1"
          :error="!!errors.text"
      />
      <v-select
          label="Действие"
          v-model="pushNotification.action"
          :items="actionList"
          item-value="value"
          item-text="text"
          :error-messages="errors.action"
          :error-count="1"
          :error="!!errors.subject"
          clearable
      />

      <div v-if="pushNotification.schedules && pushNotification.schedules.length">
        <p class="text-subtitle-1 mt-2">Планировщик</p>
        <div
            class="d-flex"
            :key="`${schedule.id}-${i}`"
            v-for="(schedule,i) in pushNotification.schedules"
        >
          <DateTimeForm
              v-model="schedule.time"
              dense

          />
          <v-btn icon @click="remove(i)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </div>

      </div>
      <v-alert type="info" v-else>
        Уведомление отправится сразу после утверждения
      </v-alert>
      <v-btn class="mt-2" type="primary" small @click="addSchedule()">Добавить время отправки</v-btn>
    </v-card-text>
    <v-card-actions>
      <v-spacer/>
      <v-btn color="primary" @click="save()">Сохранить</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import DateTimeForm from "@/components/Order/QuestionTypes/DateTimeForm";
import moment from "moment";

export const PushNotificationActionList = [
  {
    value: 'orders_list',
    text: "Список заявок"
  },
  {
    value: 'orders_map',
    text: "Карта заявок"
  },
  {
    value: 'companies_list',
    text: "Список компаний"
  },
  {
    value: 'notifications_list',
    text: "Уведомления"
  },
];

export default {
  name: "PushNotificationForm",
  components: {DateTimeForm},
  props: ['value'],
  data() {
    return {
      pushNotification: this.value,
      errors: {},
      actionList: PushNotificationActionList,
    }
  },
  created() {
    this.pushNotification.schedules?.map((i) => {
      i.time = moment(i.time).format("YYYY-MM-DD HH:mm")
    });
  },
  methods: {
    remove(i) {
      this.pushNotification.schedules?.splice(i, 1);
    },
    addSchedule() {
      if (!this.pushNotification.schedules) {
        this.$set(this.pushNotification, 'schedules', []);
      }
      this.pushNotification.schedules.push({
        id: new Date().getTime(),
        time: moment().format("YYYY-MM-DD HH:mm")
      })
    },
    save() {
      this.errors = {};
      this.pushNotification.schedules?.map((i) => {
        i.time = moment(i.time).utc().format("YYYY-MM-DD HH:mm")
      })
      if (this.pushNotification.id) {
        this.update();
      } else {
        this.store();
      }
    },
    store() {
      this.$http.post(`push-notifications`, {...this.pushNotification, type: "push"}).then(r => {
        this.$emit("input", r.body.pushNotification);
        this.$emit("created", r.body.pushNotification);
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    update() {
      this.$http.put(`push-notifications/${this.pushNotification.id}`, this.pushNotification).then(r => {
        this.$emit("input", r.body.pushNotification);
        this.$emit("updated", r.body.pushNotification);
      }).catch(err => {
        this.errors = err.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>