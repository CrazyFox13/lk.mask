<template>
  <div>
    <input @change="fileSelected" ref="fileInput" type="file" :multiple="multiple"/>
    <el-button link @click="onUploadTrigger" :disabled="loading">
      <SvgIcon :name="icon?icon:'upload-input'" class="mr-2"/>
      {{ hideLabel ? '' : label ? label : 'Добавить файлы' }}
    </el-button>
  </div>
</template>

<script>
import SvgIcon from "~/components/SvgIcon";
import FileUploader from "~/composables/fileUploader";

export default {
  name: "FileUploader",
  components: {SvgIcon},
  props: ['label', 'error', 'icon', 'hideLabel', 'multiple'],
  data() {
    return {
      loading: false,
    }
  },
  // UPLOADER
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
    onUploadTrigger() {
      this.$refs['fileInput'].click();
    },
    init() {
      try {
        this.$refs.input.$refs['input-slot'].children[0].click()
      } catch (e) {
        console.error("INIT ERR", e)
      }
    },
    async fileSelected(e) {
      const files = e.target.files;
      if (files.length === 0) return;

      const uploader = new FileUploader();
      uploader.on("uploaded", (file) => {
        this.$emit("file_data", file);
      });

      this.loading = true;
      for (let file of files) {
        await uploader.uploadFile(file);
      }
      this.loading = false;
    },
  }
}
</script>

<style scoped>
input {
  display: none;
}
</style>