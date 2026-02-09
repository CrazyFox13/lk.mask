<template>
  <div class="chat-container">
    <div class="chat-header">
      <div class="text-h5">Чат претензия №{{ report_id }}</div>
      <el-button @click="close()" circle text>
        <SvgIcon name="close"/>
      </el-button>
    </div>
    <div class="chat-body" id="chat">
      <ChatMessage
          v-for="message in sortedMessages"
          :key="message.id"
          :message="message"
          :need-date="needDate(message)"
      />
    </div>
    <div class="chat-footer">
      <!--<el-button circle text>
        <SvgIcon name="plus"/>
      </el-button>-->
      <FileUploader
          label=""
          icon="plus"
          :hide-label="true"
          @file_data="fileUploaded"
          :multiple="false"
      />
      <el-input
          class="mb-0"
          placeholder="Введите сообщение"
          v-model="newMessage.text"
          @keydown.enter="send()"
      />
      <el-button circle text @click="send()">
        <SvgIcon name="send-circle"/>
      </el-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import SvgIcon from "~/components/SvgIcon.vue";
import {apiFetch, computed, nextTick, onMounted, useAsyncData, ruMoment, watch, useRuntimeConfig} from "#imports";
import {useDeviceStore} from "~/stores/device";
import debounce from "lodash.debounce";
import ChatMessage from "~/components/Profile/Reports/ChatMessage.vue";
import FileUploader from "~/components/Common/Forms/FileUploader.vue";
import Pusher from 'pusher-js';
import {useAuthStore} from "~/stores/user";
import {storeToRefs} from "pinia";

interface IAuthor {
  id: number
  avatar: string
  last_online_datetime: Date
  name: string
  role: "author" | "support"
  surname: string
}

interface IMessage {
  id?: number
  text: string | undefined
  file_type: string | undefined
  file_url: string | undefined
  is_mine?: boolean
  created_at?: Date
  author?: IAuthor
}

const props = defineProps(['chat_id', 'report_id']);
const emit = defineEmits(['close']);
const {isMobile} = useDeviceStore();
const body = ref<any>();
const chatElement = ref<any>();
const pagesCount = ref(1);
const page = ref(1);
const messages = ref<IMessage[]>([]);
const newMessage = ref<IMessage>({
  text: undefined,
  file_type: undefined,
  file_url: undefined
});

onMounted(() => {
  if (process.client && isMobile) {
    body.value = document.body;
    body.value.classList.add('m-chat');
    setHeight();
    window.addEventListener('resize', setHeight);
  }
});

const close = () => {
  body.value ? body.value.classList.remove('m-chat') : ``;
  emit("close");
}

const setHeight = () => {
  let vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`)
}

const getMessages = async () => {
  const data = await apiFetch(`chats/${props.chat_id}/messages?take=30&page=${page.value}`);
  messages.value = messages.value.concat(data.messages);
  pagesCount.value = data.pagesCount;

  await nextTick(() => {
    if (page.value === 1) {
      mountPosition()
    } else {
      scrollTo(`#msg-${data.messages[0]?.id}`)
    }
  })
}

const mountPosition = async () => {
  scrollTo(firstMsgId.value);
  await nextTick(() => {
    listenScrolling();
  })
}
const onSent = async (message: IMessage) => {
  messages.value.push(message);
  newMessage.value = {
    text: undefined,
    file_type: undefined,
    file_url: undefined
  }
  //this.removeFile();
  await nextTick(() => {
    scrollTo(firstMsgId.value);
  })
}
const scrollTo = (selector: string) => {
  const element = document.querySelector(selector);
  if (!element) return;
  element.scrollIntoView();
}

const sortedMessages = computed(() => {
  return JSON.parse(JSON.stringify(messages.value)).sort((a: IMessage, b: IMessage) => a.id! - b.id!);
});

const listenScrolling = () => {
  chatElement.value = document.getElementById('chat');
  chatElement.value.addEventListener('scroll', debounce(() => {
    onScrollChat();
  }), 100)
}

const onScrollChat = () => {
  if (chatElement.value.scrollTop === 0 && pagesCount.value > page.value) {
    page.value++;
  }
}

const firstMsgId = computed(() => {
  return `#msg-${messages.value[0]?.id}`
});

const send = () => {
  apiFetch(`chats/${props.chat_id}/messages`, {
    method: "POST",
    body: newMessage.value
  }).then(({message}) => onSent(message)).catch(({body}) => {
    console.error(body)
  })
}

const needDate = (message: any) => {
  const idx = sortedMessages.value.indexOf(message);
  const prevMsg = sortedMessages.value[idx - 1];
  if (!prevMsg) return true;
  return ruMoment(prevMsg.created_at).format("YYYY-MM-DD") !== ruMoment(message.created_at).format("YYYY-MM-DD");
}

watch(page, () => {
  getMessages();
})

useAsyncData(() => getMessages());

const fileUploaded = (file: any) => {
  newMessage.value.file_type = file.type;
  newMessage.value.file_url = file.url;
  send();
}

const config = useRuntimeConfig();
const {getToken} = useAuthStore();
const pusher = new Pusher(config.public.pusherAppKey, {
  cluster: config.public.pusherCluster,
  httpHost: config.public.pusherHost,
  wsHost: config.public.pusherHost,
  httpPort: Number(config.public.pusherPort),
  wsPort: config.public.pusherSchema === "https" ? 443 : 6001,
  forceTLS: config.public.pusherSchema === "https",
  channelAuthorization: {
    transport: "ajax",
    endpoint: `${config.public.pusherSchema}://${config.public.pusherHost}:${config.public.pusherPort}/api/broadcasting/auth-presence`,
    headers: {
      'accept': 'application/json',
      'Authorization': `Bearer ${getToken()}`,
    }
  },
});

const {user} = storeToRefs(useAuthStore())
const channel = pusher.subscribe(`presence-chat-${props.chat_id}`);
channel.bind("App\\Events\\NewChatMessage", async (data: any) => {
  const {chatId, messageId, authorId} = data;
  if (authorId === user.value!.id) return;
  const {message: newMsg} = await apiFetch(`chats/${chatId}/messages/${messageId}`);
  messages.value.push(newMsg);
  await nextTick(() => {
    scrollTo(firstMsgId.value);
  })
});

</script>

<style lang="scss">
.chat-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: white;
  z-index: 3;

  .chat-header {
    padding: 5px 15px;
    border-bottom: 1px solid rgba(167, 180, 204, 0.3);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .chat-body {
    height: calc(100vh - 130px);
    height: calc(var(--vh, 1vh) * 100 - 130px);
    padding: 5px 15px;
    overflow-y: auto;
  }

  .chat-footer {
    padding: 5px 15px;
    border-top: 1px solid rgba(167, 180, 204, 0.3);
    display: flex;
    justify-content: space-around;
    align-items: center;

    .el-input__wrapper {
      background: none;
    }
  }

  @media (min-width: 992px) {
    max-width: 450px;
    max-height: 620px;
    left: auto;
    top: auto;
    bottom: 30px;
    right: 30px;
    box-shadow: 0 4px 20px 1px rgba(23, 26, 27, 0.16);
    border-radius: 10px;

    .chat-body {
      height: 500px;
    }
  }
}
</style>