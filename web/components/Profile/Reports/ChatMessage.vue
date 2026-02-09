<template>
  <div
      class="message-item"
      v-bind:class="{'is-mine':message.is_mine}"
      :id="`msg-${message.id}`"
  >

    <div v-if="needDate" class="text-center text-h6">
      {{ ruMoment(message.created_at).format("DD MMM YYYY") }}
    </div>

    <div class="message-author text-sub-subtitle text-gray" v-if="message.author">
      {{ message.author.role === 'support' ? 'Арбитр' : `${message.author.name} ${message.author.surname}` }}
    </div>
    <div class="message-content">
      <el-avatar v-if="!message.is_mine" :src="message.author.avatar"/>
      <div class="message-bubble">
        <MessageDocument v-if="message.file_type==='doc'" :url="message.file_url"/>
        <MessageImage v-else-if="message.file_type==='img'" :url="message.file_url"/>
        <MessageText v-else :text="message.text"/>
        <div class="text-sub-subtitle text-gray text-right">{{ ruMoment(message.created_at).format("HH:mm") }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ruMoment} from "#imports";
import MessageText from "~/components/Profile/Reports/MessageText.vue";
import MessageImage from "~/components/Profile/Reports/MessageImage.vue";
import MessageDocument from "~/components/Profile/Reports/MessageDocument.vue";

const props = defineProps(['message', 'needDate']);
</script>

<style scoped lang="scss">
.message-item {
  margin-bottom: 10px;

  .message-author {
    padding-left: 40px;
  }


  .message-content {
    display: flex;
    align-items: flex-start;
    column-gap: 8px;

    .el-avatar {
      margin-top: 3px;
      width: 32px;
      height: 32px;
      min-width: 32px;
      min-height: 32px;
    }

    .message-bubble {
      background: #F3F5F9;
      border-radius: 6px;
      padding: 8px;
    }
  }

  &.is-mine {
    text-align: right;

    .message-author {
      padding-left: 0;
    }

    .message-content {
      justify-content: end;

      .message-bubble {
        background: #E6EFF9;
        border-radius: 6px;
        padding: 8px;
      }
    }
  }
}
</style>