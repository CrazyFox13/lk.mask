<template>
  <v-file-input
      ref="input"
      :label="label"
      :prepend-icon="icon?'mdi-paperclip':''"
      v-model="file"
      @change="fileSelected()"
      :hide-input="icon"
      :error-count="1"
      :error-messages="error"
      :error="!!error"
      :loading="loading"
      :hint="hint"
      :persistent-hint="!!hint"
  >
    <template #prepend-inner>
      <slot name="prepend-inner"/>
    </template>
  </v-file-input>
</template>

<script>
export default {
  name: "FileUploader",
  props: ['label', 'error', 'icon','hint'],
  data() {
    return {
      file: null,
      // UPLOADER
      chunks: [],

      uploaded: 0,
      upload_id: null,
      key: null,

      chunkSize: 1024 * 1024 * 6,
      loading: false,
    }
  },
  watch: {
    // uploader
    chunks(n) {
      if (n.length > 0) {
        this.uploadPart();
      }
    }
  },
  // UPLOADER
  computed: {
    progress() {
      return Math.floor((this.uploaded * 100) / this.file.size);
    },
    formData() {
      let formData = new FormData;
      formData.append('part', this.chunks[0], `${this.file.name}`);
      formData.append('PartNumber', (this.uploaded + 1))
      formData.append('Key', this.key)

      /*if (this.chunks.length === 1) {
        formData.append('is_last', true);
      }
      if (this.upload_id) {
        formData.append('upload_id', this.upload_id);
      }*/
      return formData;
    },
  },
  created() {
    if (this.link) {
      let initiator = this.$parent.$refs[this.link];
      if (initiator) {
        initiator.$el.addEventListener("click", () => {
          this.init();
        })
      }
    }
  },
  methods: {
    init() {
      try {
        this.$refs.input.$refs['input-slot'].children[0].click()
      } catch (e) {
        console.error("INIT ERR", e)
      }
    },
    uploadPart() {
      this.$http.post(`upload/multipart/${this.upload_id}`, this.formData, {
        headers: {
          'Content-Type': 'application/octet-stream'
        },
      }).then(r => {
        if (r.body.UploadId) {
          this.upload_id = r.body.UploadId;
        }
        if (r.body.Key) {
          this.key = r.body.Key;
        }

        this.uploaded++;
        this.chunks.shift();

        if (this.chunks.length === 0) {
          this.completeMultipartUpload();
        }
      }).catch(err => {
        console.log(err.body);
      });
    },
    async fileSelected() {
      if (!this.file) {
        return;
      }

      if (this.file.size <= this.chunkSize) {
        return await this.regularUpload();
      }
      this.uploaded = 0;
      await this.createMultipartUpload();
      //return;

      let chunks = Math.ceil(this.file.size / this.chunkSize);

      for (let i = 0; i < chunks; i++) {
        let el = this.file.slice(
            i * this.chunkSize, Math.min(i * this.chunkSize + this.chunkSize, this.file.size), this.file.type
        );
        this.chunks.push(el);
      }
    },
    async createMultipartUpload() {
      this.loading = true;
      return this.$http.post(`upload/multipart`, {
        filename: this.file.name,
        file_type: this.file.type
      }).then(r => {
        this.key = r.body.Key;
        this.upload_id = r.body.UploadId;
      }).catch(err => {
        console.log(err.body);
      })
    },
    async completeMultipartUpload() {
      const {body} = await this.$http.get(`upload/multipart/${this.upload_id}?Key=${this.key}`);
      return this.$http.post(`upload/multipart/${this.upload_id}/complete`, {
        Key: this.key,
        Parts: body.parts,
      }).then(r => {
        this.$emit('input', r.body.file.url);
        this.$emit('file_data', {
          url: r.body.file.url,
          file_type: this.file.type,
          file_size: this.file.size,
          file_name: this.file.name,
        });
        this.loading = false;
      })
    },
    regularUpload() {
      const fd = new FormData;
      fd.append('file', this.file);
      this.loading = true;
      this.$http.post(`upload`, fd).then(r => {
        this.$emit('input', r.body.file.url)
        this.$emit('file_data', {
          url: r.body.file.url,
          file_type: this.file.type.includes('video') ? 'video' : this.file.type.includes('image') ? 'img' : 'doc',
          file_size: this.file.size,
          file_name: this.file.name,
        });
        this.loading = false;
      })
    }
  }
}
</script>

<style scoped>

</style>