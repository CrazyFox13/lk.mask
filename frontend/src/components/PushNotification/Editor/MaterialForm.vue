<template>
  <div>
    <div class="d-block mt-5 min-h">
      <ckeditor :editor="editor" v-model="editorData" :config="editorConfig"/>
    </div>
    <v-btn class="mt-5" color="primary" @click="save()">Сохранить</v-btn>
  </div>
</template>

<script>
import CKEditor from '@ckeditor/ckeditor5-vue2';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import uploader from "@/plugins/uploader";

export default {
  name: "MaterialForm",
  components: {
    // Use the <ckeditor> component in this view.
    ckeditor: CKEditor.component,
  },
  props: ['pushNotification'],
  data() {
    return {
      editor: ClassicEditor,
      editorData: this.pushNotification.material,
      editorConfig: {
        // The configuration of the editor.
        language: 'ru',
        extraPlugins: [uploader,],
      }
    }
  },
  methods: {
    save() {
      this.$http.post(`push-notifications/${this.pushNotification.id}/material`, {
        material: this.editorData,
      }).then(() => {
        this.$emit("updated")
      })
    }
  }
}
</script>

<style>
.ck-content {
  height: 300px;
}
</style>