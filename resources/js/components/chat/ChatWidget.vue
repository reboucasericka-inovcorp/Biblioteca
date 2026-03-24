<template>
  <button
    @click="openChat"
    class="fixed bottom-5 right-5 z-[1000] bg-indigo-600 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-xl hover:scale-105 hover:bg-indigo-700 transition"
    aria-label="Abrir chat"
  >
    💬
  </button>
</template>

<script setup>
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
</script>