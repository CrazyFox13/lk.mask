<template>
  <div>
    <v-alert outlined type="info" v-if="groups.length===0">Портфолио не загружено</v-alert>

    <v-slide-group
        v-else
        v-model="groupId"
        class="pa-4 mb-3"
        show-arrows
    >
      <v-slide-item
          v-for="group in groups"
          :key="group.id"
          v-slot="{  }"
      >
        <v-hover class="elevation-3 my-2 mr-4 " v-slot="{ hover }">
          <div class="group-container cursor-pointer"
               @click="$router.push(`/companies/${company.id}/photo-groups/${group.id}`)">
            <div v-if="group.photos.length>0" class="group-grid">
              <v-img v-for="photo in group.photos" :key="photo.id" :src="photo.url"/>
            </div>
            <div v-else class="no-photo">Нет фото</div>
            <v-expand-transition>
              <div
                  v-if="hover"
                  class="d-flex transition-fast-in-fast-out primary darken-2 v-card--reveal white--text"
                  style="height: 100%;"
              >
                {{ group.title }}
              </div>
            </v-expand-transition>
          </div>
        </v-hover>
      </v-slide-item>
    </v-slide-group>
  </div>
</template>

<script>
export default {
  name: "PhotoGroups",
  props: ['company'],
  data() {
    return {
      groups: [],
      groupId: undefined,
    }
  },
  created() {
    this.getPhotoGroups();
  },
  methods: {
    getPhotoGroups() {
      this.$http.get(`companies/${this.company.id}/photo-groups`).then(r => {
        this.groups = r.body.photoGroups;
      })
    }
  }
}
</script>

<style scoped>
.v-card--reveal {
  align-items: center;
  bottom: 0;
  justify-content: center;
  /* opacity: .5;*/
  position: absolute;
  width: 100%;
}

.group-container {
  width: 150px;
  height: 100px;
  position: relative;
}

.group-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
}

.no-photo {
  position: absolute;
  top: calc(50% - 12px);
  left: 0;
  right: 0;
  text-align: center;
}

.cursor-pointer {
  cursor: pointer;
}
</style>