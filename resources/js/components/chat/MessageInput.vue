<template>
  <form class="p-4 border-t border-gray-200 bg-white flex items-center gap-2 relative" @submit.prevent="onSubmit">
    <button
      type="button"
      class="rounded-full border border-gray-300 w-10 h-10 text-lg hover:bg-gray-50 transition-colors"
      :disabled="!canSend"
      @click="showEmojiPicker = !showEmojiPicker"
    >
      😊
    </button>
    <label
      class="rounded-full border border-gray-300 w-10 h-10 text-lg flex items-center justify-center hover:bg-gray-50 transition-colors cursor-pointer"
      :class="!canSend ? 'opacity-50 pointer-events-none' : ''"
    >
      📎
      <input
        class="hidden"
        type="file"
        accept="image/*"
        @change="onFileChange"
      />
    </label>
    <input
      v-model="body"
      @input="onInput"
      type="text"
      class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100"
      placeholder="Digite uma mensagem..."
      :disabled="!canSend"
    />
    <button
      type="submit"
      class="rounded-full bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition-colors disabled:opacity-60"
      :disabled="!canSend"
    >
      Enviar
    </button>

    <emoji-picker
      v-if="showEmojiPicker"
      class="absolute bottom-16 left-0 z-20"
      @emoji-click="onEmojiClick"
    ></emoji-picker>
  </form>
</template>

<script setup>
import 'emoji-picker-element';
import { ref } from 'vue';

defineProps({
  canSend: { type: Boolean, default: false },
});

const emit = defineEmits(['send', 'typing', 'upload-image']);
const body = ref('');
const showEmojiPicker = ref(false);

function onInput() {
  if (!body.value.trim()) return;
  emit('typing');
}

function onSubmit() {
  const message = body.value.trim();
  if (!message) return;
  emit('send', message);
  body.value = '';
}

function onEmojiClick(event) {
  const emoji = event?.detail?.unicode ?? '';
  if (!emoji) return;
  body.value += emoji;
  emit('typing');
}

function onFileChange(event) {
  const file = event.target?.files?.[0];
  if (!file) return;
  emit('upload-image', file);
  event.target.value = '';
}
</script>
