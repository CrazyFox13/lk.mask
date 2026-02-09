<template>
  <div>
    <div id="chat">
      <MessageItem
          :key="message.id"
          v-for="message in sortedMessages"
          :value="message"
          :need_date="needDate(message)"
          @deleted="onMessageDeleted(message)"
      />
      <div v-if="previewFile.url" class="img-preview-container">
        <v-btn class="mb-2" icon outlined x-small @click="removeFile()">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-img v-if="previewFile.file_type==='img'" width="100" max-width="100" max-height="100" contain
               :src="previewFile.url"/>
        <div v-else>
          <v-chip>{{ previewFile.file_name }}</v-chip>
        </div>
      </div>
    </div>

    <v-alert v-if="!entered" type="info" outlined>
      <v-row align="center">
        <v-col class="grow">
          Вы не состоите в чате. Войдите, чтобы иметь возможность писать.
        </v-col>
        <v-col class="shrink">
          <v-btn color="primary" @click="enterChat()">Войти</v-btn>
        </v-col>
      </v-row>
    </v-alert>
    <SendForm v-else @sent="onSent" @fileUploaded="onFileUploaded" :chat="chat"/>
  </div>
</template>

<script>
import moment from "moment";
import MessageItem from "@/components/Chat/MessageItem";
import SendForm from "@/components/Chat/SendForm";
import Pusher from 'pusher-js';

const {debounce} = require('lodash')
export default {
  name: "ReportChat",
  components: {SendForm, MessageItem},
  props: ['chat'],
  data() {
    return {
      moment: moment,
      messages: [],
      page: 1,
      pagesCount: 1,
      chatElement: undefined,
      entered: !!this.chat.user_id,
      previewFile: {},
    }
  },
  created() {
    this.getMessages();
  },
  computed: {
    sortedMessages() {
      return JSON.parse(JSON.stringify(this.messages)).sort((a, b) => a.id - b.id);
    },
    firstMsgId() {
      return `#msg-${this.messages[0]?.id}`
    }
  },
  watch: {
    page() {
      this.getMessages();
    },
  },
  methods: {
    getMessages() {
      this.$http.get(`chats/${this.chat.id}/messages?take=7&page=${this.page}`).then(r => {
        this.messages = this.messages.concat(r.body.messages);
        this.pagesCount = r.body.pagesCount;
        this.subscribeChannel();
        this.$nextTick(() => {
          if (this.page === 1) {
            this.mountPosition()
          } else {
            this.scrollTo(`#msg-${r.body.messages[0]?.id}`)
          }
        })
      })
    },
    mountPosition() {
      this.scrollTo(this.firstMsgId);
      this.$nextTick(() => {
        this.listenScrolling();
      })
    },
    onSent(message) {
      this.messages.push(message);
      this.removeFile();
      this.$nextTick(() => {
        this.scrollTo(this.firstMsgId);
      })
    },
    needDate(message) {
      const idx = this.sortedMessages.indexOf(message);
      const prevMsg = this.sortedMessages[idx - 1];
      if (!prevMsg) return true;
      return moment(prevMsg.created_at).format("YYYY-MM-DD") !== moment(message.created_at).format("YYYY-MM-DD");
    },
    scrollTo(selector) {
      const element = document.querySelector(selector);
      if (!element) return;
      element.scrollIntoView();
    },
    listenScrolling() {
      this.chatElement = document.getElementById('chat');
      this.chatElement.addEventListener('scroll', debounce((e) => {
        this.onScrollChat(e);
      }, 100))
    },
    onScrollChat() {
      if (this.chatElement.scrollTop === 0 && this.pagesCount > this.page) {
        this.page++;
      }
    },
    enterChat() {
      this.$http.post(`chats/${this.chat.id}/enter`).then(() => {
        this.entered = true;
      })
    },
    onFileUploaded(file) {
      this.previewFile = file
    },
    removeFile() {
      this.previewFile = {};
    },
    onMessageDeleted(message) {
      this.messages.splice(this.messages.indexOf(message), 1)
    },
    subscribeChannel() {
      const {
        VUE_APP_PUSHER_APP_KEY,
        VUE_APP_PUSHER_CLUSTER,
        VUE_APP_PUSHER_APP_HOST,
        VUE_APP_PUSHER_APP_PORT,
        VUE_APP_PUSHER_APP_SCHEMA
      } = process.env;

      const pusher = new Pusher(VUE_APP_PUSHER_APP_KEY, {
        cluster: VUE_APP_PUSHER_CLUSTER,
        httpHost: VUE_APP_PUSHER_APP_HOST,
        wsHost: VUE_APP_PUSHER_APP_HOST,
        httpPort: Number(VUE_APP_PUSHER_APP_PORT),
        wsPort: VUE_APP_PUSHER_APP_SCHEMA === "https" ? 443 : 6001,
        forceTLS: VUE_APP_PUSHER_APP_SCHEMA === "https",
        channelAuthorization: {
          transport: "ajax",
          endpoint: `${VUE_APP_PUSHER_APP_SCHEMA}://${VUE_APP_PUSHER_APP_HOST}:${VUE_APP_PUSHER_APP_PORT}/api/broadcasting/auth-presence`,
          headers: {
            'accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.token}`,
          }
        },
      });

      const channel = pusher.subscribe(`presence-chat-${this.chat.id}`);
      channel.bind("App\\Events\\NewChatMessage", async data => {
        const {chatId, messageId, authorId} = data;
        if (authorId === this.$store.state.user.id) return;
        const {body} = await this.$http.get(`chats/${chatId}/messages/${messageId}`);
        this.messages.push(body.message);

        this.$nextTick(() => {
          this.scrollTo(this.firstMsgId);
        })
      });
    }
  }
}
</script>

<style lang="scss">
#chat {
  height: 400px;
  overflow-y: scroll;
  padding: 10px 20px 0 10px;
  border: 1px solid gray;
  margin-bottom: 10px;
  border-radius: 3px;
  position: relative;

  .date {
    width: 100%;
    text-align: center;
  }
}

.msg {
  height: fit-content !important;

  .v-chip__content {
    height: fit-content;
    white-space: normal;
    flex-flow: column;
    align-items: flex-start;
  }
}

.msg-container {
  display: flex;
  flex-flow: column;
  align-items: flex-start;

  &.mine-msg {
    align-items: flex-end;
  }
}

.msg-date {
  margin-top: 7px;
  margin-left: auto;
}

.img-preview-container {
  position: sticky;
  bottom: 0;
  z-index: 3;
  width: 100%;
  border: 1px solid;
  border-width: 2px 1px 0px 0px;
  padding: 10px 15px;
  margin-left: -11px;
  border-radius: 0 15px 0px 0px;
  background: #ffffff;
  margin-top: 10px;
}
</style>