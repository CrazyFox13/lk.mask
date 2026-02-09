<template>
  <div>
    <v-breadcrumbs
        :items="breadcrumps"
        divider="-"
    />
    <div class="d-flex justify-space-between">
      <div class="text-h4">{{ group.title }}</div>
      <v-btn icon color="error" @click="deleteGroup()">
        <v-icon>mdi-delete</v-icon>
      </v-btn>
    </div>
    <div class="text-h6">{{ group.company ? group.company.title : '' }}</div>
    <div class="text-subtitle-1">Найдено: {{ totalCount }}</div>
    <v-row v-if="totalCount>0" class="mt-4">
      <v-col cols="12" sm="6" md="4" lg="3" v-for="photo in photos" :key="photo.id">
        <v-card @click="show(photo)" height="100%" class="pointer">
          <v-img :src="photo.url"/>
          <v-card-text>{{ short(photo.description) }}</v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-alert type="info" outlined v-else>В альбоме нет фото</v-alert>
    <v-pagination class="mt-4" v-model="page" :length="pagesCount"/>

    <v-dialog v-model="photoDialog" max-width="700">
      <v-card v-if="widePhoto">
        <v-img :src="widePhoto.url"/>
        <v-card-text class="mt-2">{{ widePhoto.description }}</v-card-text>
        <v-card-actions>
          <v-btn text @click="photoDialog=false">Закрыть</v-btn>
          <v-spacer/>
          <v-btn icon color="error" @click="deletePhoto(widePhoto)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import Swal from "sweetalert2-khonik";

export default {
  name: "PhotosView",
  data() {
    return {
      company_id: Number(this.$route.params.id),
      group_id: Number(this.$route.params.groupId),
      photos: [],
      group: {},
      page: 1,
      pagesCount: 1,
      totalCount: 1,

      widePhoto: undefined,
      photoDialog: false,
    }
  },
  created() {
    this.getGroup();
    this.getPhotos();
  },
  watch: {
    page() {
      this.getPhotos();
    }
  },
  computed: {
    breadcrumps() {
      return [
        {
          text: 'Компании',
          disabled: false,
          href: '/admin/companies',
        },
        {
          text: `${this.group?.company?.title}`,
          disabled: false,
          href: `/admin/companies/${this.company_id}`,
        },
        {
          text: `${this.group?.title}`,
          disabled: true,
          href: `#`,
        },

      ]
    }
  },
  methods: {
    show(photo) {
      this.widePhoto = photo;
      this.photoDialog = true;
    },
    short(str) {
      if (!str) return null;
      const len = 60;
      if (str.length <= 60) return str;
      return str.split('').splice(0, len).join('') + "..."
    },
    getGroup() {
      this.$http.get(`companies/${this.company_id}/photo-groups/${this.group_id}`).then(r => {
        this.group = r.body.photoGroup;
      }).catch(() => {
        this.$router.replace(`/companies/${this.company_id}`)
      })
    },
    getPhotos() {
      this.$http.get(`companies/${this.company_id}/photo-groups/${this.group_id}/photos?&page=${this.page}`).then(r => {
        this.photos = r.body.photos;
        this.pagesCount = r.body.pagesCount;
        this.totalCount = r.body.totalCount;
      }).catch(() => {
        this.$router.replace(`/companies/${this.company_id}`)
      })
    },
    deleteGroup() {
      Swal.fire({
        title: "Вы уверены, что хотите удалить альбом?",
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`companies/${this.company_id}/photo-groups/${this.group_id}`).then(() => {
            this.$router.push(`/companies/${this.company_id}`);
          })
        }
      })
    },
    deletePhoto(widePhoto) {
      Swal.fire({
        title: "Вы уверены, что хотите удалить фото?",
        showDenyButton: true,
        denyButtonText: `Удалить`,
        showCancelButton: true,
        cancelButtonText: 'Отменить',
        showCloseButton: false,
        showConfirmButton: false,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isDenied) {
          this.$http.delete(`companies/${this.company_id}/photo-groups/${this.group_id}/photos/${widePhoto.id}`).then(() => {
            this.photos.splice(this.photos.indexOf(widePhoto), 1);
            this.photoDialog = false;
          })
        }
      })
    }
  }
}
</script>

<style scoped>
.pointer {
  cursor: pointer;
}
</style>