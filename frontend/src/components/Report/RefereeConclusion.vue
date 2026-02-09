<template>
  <div>
    <div class="text-h5 mb-3">Заключение арбитра</div>
    <v-textarea
        label="Введите текст"
        dense
        v-model="text"
        :error-messages="errors.referee_conclusion"
        :error-count="1"
        :error="!!errors.referee_conclusion"
        outlined
    />
    <v-btn color="primary" @click="save()">Сохранить</v-btn>
  </div>
</template>

<script>
import Swal from "sweetalert2-khonik";

export default {
  name: "RefereeConclusion",
  props: ['report'],
  data() {
    return {
      text:this.report.referee_conclusion,
      errors:{}
    }
  },
  methods:{
    save(){
      this.errors={};
      this.$http.post(`reports/${this.report.id}/conclusion`,{
        referee_conclusion:this.text,
      }).then(()=>{
        Swal.fire("Заключение сохранено")
      }).catch(r=>{
        this.errors = r.body.errors;
      })
    }
  }
}
</script>

<style scoped>

</style>