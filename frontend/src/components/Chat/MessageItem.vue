<template>
  <div
      class="mb-3 msg-container"
      v-bind:class="{'mine-msg':message.is_mine,'mb-24':!actions}"
      :key="message.id"
      @mouseover="actions=true"
      @mouseleave="actions=false"
  >
    <div v-if="need_date" class="date">{{ moment(message.created_at).format("DD MMM YYYY") }}</div>
    <div class="text-caption" v-if="message.author">
      {{ message.author.name }} {{ message.author.surname }} ({{ labels[message.author.role] }})
    </div>
    <div class="text-caption" v-else><i>Пользователь удалён</i></div>
    <v-chip class="msg px-4 py-2" :id="`msg-${message.id}`" :color="message.is_mine?'info':''">
      <div v-if="isImage(value)">
        <v-skeleton-loader
            v-if="!imageLoaded"
            type="image"
            width="200"
            height="200"
        />
        <v-img
            v-else
            contain
            max-width="200"
            max-height="200"
            :src="value.file_url"
            @click="imageDialog=true"
            class="cursor-pointer"
        />
      </div>
      <div v-else-if="value.file_type">
        <v-chip :href="value.file_url" target="_blank">{{ value.file_url }}</v-chip>
      </div>
      <div class="mt-2">
        {{ value.text }}
      </div>
      <div class="text-caption msg-date">
        {{ moment(message.created_at).format("HH:mm") }}
      </div>
    </v-chip>
    <div v-if="actions">
      <v-btn x-small icon color="warning" class="mr-2" @click="editDialog=true">
        <v-icon>mdi-pencil</v-icon>
      </v-btn>
      <v-btn x-small icon color="error" @click="deleteMessage()">
        <v-icon>mdi-delete</v-icon>
      </v-btn>
    </div>
    <v-dialog max-width="700" v-model="imageDialog">
      <v-img :src="message.file_url" contain/>
    </v-dialog>
    <v-dialog max-width="400" v-model="editDialog">
      <v-card>
        <v-card-title>Изменить сообщение</v-card-title>
        <v-card-text>
          <v-img
              v-if="isImage(message)"
              contain
              max-height="200"
              :src="message.file_url"
          />
          <div v-else-if="value.file_type">
            <v-chip :href="value.file_url" target="_blank">{{ value.file_url }}</v-chip>
          </div>
          <div class="d-flex mt-2">
            <file-uploader
                @file_data="fileUploaded"
                v-model="message.file_url"
                :error="errors.file_url"
                :icon="true"
                class="pt-0 mt-0"/>
            <v-spacer/>
            <v-btn color="error" icon v-if="message.file_url" @click="message.file_url=null">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </div>
          <v-textarea
              label="Введите сообщение"
              v-model="message.text"
              :error="!!errors.text"
              :error-count="1"
              :error-messages="errors.text"
          />
        </v-card-text>
        <v-card-actions>
          <v-btn text @click="editDialog=false;">Закрыть</v-btn>
          <v-spacer/>
          <v-btn color="primary" @click="edit()">Изменить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2-khonik";
import FileUploader from "@/components/Common/FileUploader";

export default {
  name: "MessageItem",
  components: {FileUploader},
  props: ['value', 'need_date'],
  data() {
    return {
      moment: moment,
      imageLoaded: false,
      imageDialog: false,
      labels: {
        author: "Автор жалобы",
        target: "Ответчик",
        support: "Модератор"
      },
      actions: false,
      editDialog: false,
      message: {...this.value},
      errors: {}
    }
  },
  created() {
    if (this.isImage(this.value)) {
      let image = new Image();
      image.addEventListener('load', () => {
        this.imageLoaded = true
      });
      image.src = this.message.file_url;
    }
  },
  methods: {
    isImage(model) {
      return ['image', 'img'].includes(model.file_type)
    },
    edit() {
      this.$http.put(`chats/${this.message.chat_id}/messages/${this.message.id}`, {
        text: this.message.text,
        ...this.message.file_url ? {
          file_url: this.message.file_url,
          file_type: this.message.file_type,
        } : {}
      }).then(r => {
        this.$set(this, 'value', {
          ...this.message, ...{
            text:r.body.message.text,
            file_url:r.body.message.file_url,
            file_type:r.body.message.file_type,
          }
        });
        this.editDialog = false;
        this.imageLoaded = true;
      })
    },
    deleteMessage() {
      Swal.fire({
        title: 'Удалить сообщение?',
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`chats/${this.message.chat_id}/messages/${this.message.id}`).then(() => {
            this.$emit('deleted')
          })
        }
      });
    },
    fileUploaded(file) {
      this.message.file_type = file.file_type;
    }
  }
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.mb-24 {
  margin-bottom: 36px !important;
}
</style>