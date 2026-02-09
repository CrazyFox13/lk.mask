<template>
  <div>
    <v-breadcrumbs :items="breadcrumps" divider="-"/>
    <div>{{ type.title }}</div>
    <v-row>
      <v-col cols="12" md="6">
        <div class="d-flex justify-space-between align-center mt-6 mb-4">
          <div class="text-subtitle-1">Изменить наименование</div>
        </div>
        <v-text-field
            label="Название"
            v-model="editType.title"
            :error="!!errors.title"
            :error-count="1"
            :error-messages="errors.title"
        />

        <v-select
            label="Типы оплат"
            v-model="editType.payment_units_id"
            :items="paymentUnits"
            item-text="name"
            item-value="id"
            :error="!!errors.payment_units_id"
            :error-count="1"
            :error-messages="errors.payment_units_id"
            multiple
        />


        <v-switch
            label="Показывать в меню"
            v-model="editType.show_in_menu"
            :error="!!errors.show_in_menu"
            :error-count="1"
            :error-messages="errors.show_in_menu"
        />

        <div v-if="editType.image">
          <v-img :src="editType.image" width="120" contain />
        </div>
        <file-uploader
            v-if="editType.show_in_menu"
            label="Изображение для меню"
            v-model="editType.image"
            :error="errors.image"
        />

        <div v-if="editType.show_in_menu">
          <label class="text-gray">Цвет фона в меню</label>
          <v-color-picker
              :value="editType.color || ''"
              @update:color="(v)=>editType.color = v.hexa"
          />
        </div>

        <div class="d-flex justify-space-between align-center mt-2">
          <v-btn small color="error" @click="destroy()">Удалить</v-btn>
          <v-btn color="primary" @click="update()">Изменить</v-btn>
        </div>
      </v-col>
      <v-col cols="12" md="6">
        <div class="d-flex justify-space-between align-center mt-6 mb-4">
          <div class="text-subtitle-1">Доп. поля</div>
          <v-btn small color="primary" @click="createQuestion()">Добавить поле</v-btn>
        </div>
        <v-alert v-if="questions.length===0" type="info" outlined>Доп. поля не добавлены.</v-alert>
        <v-list v-else>
          <v-list-item v-for="question in questions" :key="question.id">
            <form-question-icon :type="question.type" element="div"/>
            <v-list-item-content>
              <v-list-item-title>{{ question.label }} <b v-if="question.required" class="error--text">*</b>
              </v-list-item-title>
              <v-list-item-subtitle v-if="question.options && question.options.length">
                {{ question.options.join("; ") }}
              </v-list-item-subtitle>
            </v-list-item-content>
            <v-list-item-action>
              <v-btn icon @click="sendUp(question)">
                <v-icon>mdi-chevron-up</v-icon>
              </v-btn>
              <v-btn icon @click="sendDown(question)">
                <v-icon>mdi-chevron-down</v-icon>
              </v-btn>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon color="warning" @click="editQuestion(question)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn icon color="error" @click="destroyQuestion(question)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </v-list>
      </v-col>
    </v-row>

    <v-dialog v-model="editQuestionDialog" max-width="400">
      <FormQuestionEditor
          v-model="editQuestionItem"
          @close="editQuestionDialog=false"
          @created="onQuestionCreated"
          :group_id="group_id"
          :type_id="type_id"
          :key="editQuestionItem.id"
      />
    </v-dialog>
  </div>
</template>

<script>
import Swal from "sweetalert2-khonik";
import FormQuestionIcon from "@/components/Vehicle/FormQuestion/FormQuestionIcon";
import FormQuestionEditor from "@/components/Vehicle/FormQuestion/FormQuestionEditor";
import FileUploader from "@/components/Common/FileUploader.vue";

export default {
  name: "VehicleTypeView",
  components: {FileUploader, FormQuestionEditor, FormQuestionIcon},
  data() {
    return {
      group_id: Number(this.$route.params.id),
      type_id: Number(this.$route.params.type_id),
      type: {},
      editType: {},
      questions: [],
      errors: {},
      editQuestionDialog: false,
      editQuestionItem: {},
      paymentUnits: []
    }
  },
  created() {
    this.getPaymentUnits();
    this.getType();
    this.getQuestions();
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Категории техники',
          disabled: false,
          href: '/admin/vehicles',
        },
        {
          text: `${this.type?.group?.title}`,
          disabled: false,
          href: `/admin/vehicle-groups/${this.type?.group?.id}`,
        },
        {
          text: `${this.type?.title}`,
          disabled: true,
          href: `#`,
        },

      ]
    }
  },
  methods: {
    getPaymentUnits() {
      this.$http.get(`payment-units`).then(({body}) => {
        this.paymentUnits = body.paymentUnits;
      })
    },
    getType() {
      this.$http.get(`vehicle-groups/${this.group_id}/vehicle-types/${this.type_id}`).then(r => {
        this.type = r.body.vehicleType;
        this.editType = this.copyObject(this.type);
      })
    },
    getQuestions() {
      this.$http.get(`vehicle-groups/${this.group_id}/vehicle-types/${this.type_id}/form-questions`).then(r => {
        this.questions = r.body.formQuestions;
      })
    },

    update() {
      this.errors = {};
      this.$http.put(`vehicle-groups/${this.group_id}/vehicle-types/${this.type.id}`, this.editType).then(() => {
        this.type.title = this.editType.title;
        Swal.fire('Изменения сохранены');
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    destroy() {
      Swal.fire({
        title: 'Вы действительно хотите удалить тип?',
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`vehicle-groups/${this.group_id}/vehicle-types/${this.type.id}`).then(() => {
            this.$router.push(`/vehicle-groups/${this.group_id}`);
          })
        }
      })
    },
    onQuestionCreated(question) {
      this.questions.push(question);
    },
    createQuestion() {
      this.editQuestionItem = {};
      this.editQuestionDialog = true;
    },
    sendUp(question) {
      const idx = this.questions.indexOf(question);
      this.questions.splice(idx, 1);
      const newIdx = idx - 1;
      this.questions.splice(newIdx, 0, question);
      this.orderQuestions();
    },
    sendDown(question) {
      const idx = this.questions.indexOf(question);
      this.questions.splice(idx, 1);
      const newIdx = idx + 1;
      this.questions.splice(newIdx, 0, question);
      this.orderQuestions();
    },
    orderQuestions() {
      this.questions.forEach((i, k) => i.order = k);
      this.$http.post(`vehicle-groups/${this.group_id}/vehicle-types/${this.type_id}/form-questions/order`, {
        questions: this.questions.map(q => {
          return {
            question_id: q.id,
            order: q.order,
          }
        })
      });
    },
    editQuestion(question) {
      this.editQuestionItem = question;
      this.editQuestionDialog = true;
    },
    destroyQuestion(question) {
      Swal.fire({
        title: 'Вы действительно хотите удалить вопрос?',
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`vehicle-groups/${this.group_id}/vehicle-types/${this.type_id}/form-questions/${question.id}`).then(() => {
            this.questions.splice(this.questions.indexOf(question), 1);
          })
        }
      })
    }
  }
}
</script>

<style scoped>

</style>