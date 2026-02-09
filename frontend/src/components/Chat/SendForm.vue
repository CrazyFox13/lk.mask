<template>
  <div class="controllers">
    <v-textarea
        class="mt-2"
        outlined
        label="Введите сообщение..."
        :error="!!errors.text"
        :error-count="1"
        :error-messages="errors.text"
        v-model="msg.text"
    />
    <div class="d-flex align-center justify-space-between">
      <file-uploader
          @file_data="fileUploaded"
          v-model="msg.file_url"
          :error="errors.file_url"
          :icon="true" class="pt-0 mt-0"/>
      <v-btn color="primary" @click="send()">Отправить</v-btn>
    </div>
  </div>
</template>

<script>
import FileUploader from "@/components/Common/FileUploader";

export default {
  name: "SendForm",
  props:['chat'],
  components: {FileUploader},
  data() {
    return {
      msg: {
        text: undefined,
        file_url: undefined,
        file_type: undefined,
      },
      errors: {},
    }
  },
  methods: {
    send() {
      this.$http.post(`chats/${this.chat.id}/messages`, this.msg).then(r => {
        this.msg = {
          text: undefined,
          file_url: undefined
        };
        this.$emit('sent', r.body.message)
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
    fileUploaded(file) {
      this.msg.file_type = file.file_type;
      this.$emit('fileUploaded', file);
    }
  }
}
</script>

<style scoped>

</style>