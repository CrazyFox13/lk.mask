<template>
  <div>
    <AvatarSection/>
    <ProfileSection class="profile-section"/>
    <PasswordSection class="profile-section"/>
  </div>
</template>

<script setup lang="ts">

import AvatarSection from "~/components/Profile/Index/AvatarSection.vue";
import ProfileSection from "~/components/Profile/Index/ProfileSection.vue";
import PasswordSection from "~/components/Profile/Index/PasswordSection.vue";

</script>

<style scoped lang="scss">
.profile-section {
  margin-top: 20px;
  @media (min-width: 992px) {
    margin-top: 30px;
  }
}
</style>