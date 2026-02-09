<template>
  <div>
    <v-breadcrumbs
        :items="[ {
          text: 'Материалы',
          disabled: false,
          href: '/admin/materials',
        },
        {
          text: 'Создать страницу',
          disabled: true,
          href: '',
        },]"
    />
    <h6 class="text-h6 mb-4">Создать страницу</h6>

    <div>
      <v-text-field
          label="Заголовок"
          v-model="page.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />

      <v-text-field
          label="Адрес"
          v-model="page.path"
          :error-messages="errors.path"
          :error-count="1"
          :error="!!errors.path"
      >
        <template #prepend>
          <span>https://astt.su/</span>
        </template>
      </v-text-field>

      <div class="mt-2 mb-4">
        <ckeditor
            :editor="editor"
            v-model="page.content"
            :config="editorConfig"
        />
        <p class="text-hint error--text" v-if="errors.content">{{ errors.content }}</p>
      </div>

      <v-text-field
          label="SEO заголовок"
          v-model="page.seo_title"
          :error-messages="errors.seo_title"
          :error-count="1"
          :error="!!errors.seo_title"
      />

      <v-textarea
          label="SEO описание"
          v-model="page.seo_description"
          :error-messages="errors.seo_description"
          :error-count="1"
          :error="!!errors.seo_description"
      />

      <v-textarea
          label="SEO ключевые слова"
          v-model="page.seo_keywords"
          :error-messages="errors.seo_keywords"
          :error-count="1"
          :error="!!errors.seo_keywords"
      />


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

export default {
  name: "MaterialCreate",
  props: ['value'],
  components: {
    ckeditor: CKEditor.component
  },
  data() {
    return {
      page: {
        type: "material",
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
        this.$router.push('/materials')
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