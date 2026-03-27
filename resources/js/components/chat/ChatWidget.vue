<template>
  <button
    @click="openChat"
    class="fixed bottom-5 right-5 z-[1000] bg-indigo-600 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-xl hover:scale-105 hover:bg-indigo-700 transition relative"
    :style="widgetStyle"
    aria-label="Abrir chat"
  >
    💬
    <span
      v-if="unread > 0"
      class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-xs flex items-center justify-center"
    >
      {{ unread }}
    </span>
  </button>


  <div class="relative">
  <!-- Botão do chat -->
  <button class="btn btn-circle">
    💬
  </button>

  <!-- 🔴 BADGE -->
  <span
    v-if="unread > 0"
    class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center shadow"
  >
    {{ unread }}
  </span>
</div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const unread = ref(0);
const widgetStyle = {
  position: 'fixed',
  right: '1.25rem',
  left: 'auto',
  bottom: '1.25rem',
};

function updateUnread(event) {
  unread.value = Number(event?.detail ?? 0);
}

function openChat() {
  const isOnChatPage = window.location.pathname.startsWith('/chat');
  if (!isOnChatPage) {
    window.location.href = '/chat';
    return;
  }

  const hasReferrer = !!document.referrer;
  const sameOriginReferrer = hasReferrer && document.referrer.startsWith(window.location.origin);
  const referrerPath = sameOriginReferrer ? new URL(document.referrer).pathname : '';
  if (sameOriginReferrer && !referrerPath.startsWith('/chat')) {
    window.history.back();
    return;
  }

  window.location.href = '/dashboard';
}

onMounted(() => {
  unread.value = Number(window.chatUnreadCount ?? 0);
  window.addEventListener('chat-unread-updated', updateUnread);
});

onUnmounted(() => {
  window.removeEventListener('chat-unread-updated', updateUnread);
});
</script>