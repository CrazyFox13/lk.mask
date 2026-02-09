<template>
  <div>
    <v-breadcrumbs
        :items="[ {
          text: 'FAQ',
          disabled: false,
          href: '/admin/faq',
        },
        {
          text: 'Добавить FAQ',
          disabled: true,
          href: '',
        },]"
    />
    <h6 class="text-h6 mb-4">Создать элемент FAQ</h6>

    <div>
      <v-select
          v-model="page.path"
          label="Раздел"
          :items="faqCategories"
          item-value="path"
          item-text="label"
          :error-messages="errors.path"
          :error-count="1"
          :error="!!errors.path"
      />

      <v-text-field
          label="Заголовок"
          v-model="page.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />

      <div class="mt-2">
        <ckeditor
            :editor="editor"
            v-model="page.content"
            :config="editorConfig"
        />
        <p class="text-hint error--text" v-if="errors.content">{{ errors.content }}</p>
      </div>

      <v-switch
          v-model="page.hidden"
          label="Скрыть от показа"
      />
    </div>

    <v-btn color="primary" class="mt-2" @click="store()">Сохранить</v-btn>
  </div>
</template>

<script>
import CKEditor from '@ckeditor/ckeditor5-vue2';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import uploader from "@/plugins/uploader";
import ResourceStatuses from "@/mixins/ResourceStatuses";

export default {
  name: "FAQCreate",
  mixins: [ResourceStatuses],
  props: ['value'],
  components: {
    ckeditor: CKEditor.component
  },
  data() {
    return {
      page: {
        type: "faq",
      },
      errors: {},
      editor: ClassicEditor,
      editorConfig: {
        // The configuration of the editor.
        extraPlugins: [uploader],
      },
    }
  },
  methods: {
    store() {
      this.errors = {};
      this.$http.post(`pages`, this.page).then(() => {
        this.$router.push('/faq')
      }).catch(err => {
        this.errors = err.body.errors;
      })
    },
  }
}
</script>

<style>
.ck-editor__editable {
  height: 400px;
  overflow-x: scroll;
}
</style>