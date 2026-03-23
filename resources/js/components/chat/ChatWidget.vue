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
  window.location.href = '/chat';
}
</script>