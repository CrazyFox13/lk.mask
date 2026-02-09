<template>
  <div>
    <v-breadcrumbs
        v-if="page"
        :items="[ {
          text: page.type==='material'?'Материалы':'FAQ',
          disabled: false,
          href: `/admin/${page.type==='material'?'materials':'faq'}`,
        },
        {
          text: page.title,
          disabled: true,
          href: '',
        },]"
    />
    <h6 class="text-h6 mb-4">Редактирование</h6>

    <div v-if="page">
      <v-text-field
          label="Заголовок"
          v-model="page.title"
          :error-messages="errors.title"
          :error-count="1"
          :error="!!errors.title"
      />

      <v-text-field
          v-if="page.type==='material'"
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
      <v-select
          v-else
          v-model="page.path"
          label="Раздел"
          :items="faqCategories"
          item-value="path"
          item-text="label"
          :error-messages="errors.path"
          :error-count="1"
          :error="!!errors.path"
      />

      <div class="mt-2 mb-4">
        <ckeditor
            :editor="editor"
            v-model="page.content"
            :config="editorConfig"
        />
        <p class="text-hint error--text" v-if="errors.content">{{ errors.content }}</p>
      </div>

      <v-text-field
          v-if="page.type==='material'"
          label="SEO заголовок"
          v-model="page.seo_title"
          :error-messages="errors.seo_title"
          :error-count="1"
          :error="!!errors.seo_title"
      />

      <v-textarea
          v-if="page.type==='material'"
          label="SEO описание"
          v-model="page.seo_description"
          :error-messages="errors.seo_description"
          :error-count="1"
          :error="!!errors.seo_description"
      />

      <v-textarea
          v-if="page.type==='material'"
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

    <v-btn color="primary" class="mt-2" @click="update()">Сохранить</v-btn>
  </div>
</template>

<script>
import CKEditor from '@ckeditor/ckeditor5-vue2';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import uploader from "@/plugins/uploader";
import ResourceStatuses from "@/mixins/ResourceStatuses";

export default {
  name: "PageEdit",
  mixins: [ResourceStatuses],
  props: ['value'],
  components: {
    ckeditor: CKEditor.component
  },
  data() {
    return {
      pageId: Number(this.$route.params.id),
      page: undefined,
      errors: {},
      editor: ClassicEditor,
      editorConfig: {
        // The configuration of the editor.
        extraPlugins: [uploader],
      },
    }
  },
  created() {
    this.$http.get(`pages/${this.pageId}`).then(({body}) => {
      this.page = body.page;
    })
  },
  methods: {
    update() {
      this.errors = {};
      this.$http.put(`pages/${this.pageId}`, this.page).then(() => {
        this.$router.back()
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