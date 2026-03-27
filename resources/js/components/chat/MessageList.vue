<template>
  <div ref="messagesEl" class="flex-1 min-h-0 overflow-y-auto p-5 space-y-3 bg-slate-50 scroll-smooth">
    <TransitionGroup name="message-fade" tag="div" class="space-y-3">
      <div
        v-for="entry in groupedEntries"
        :key="entry.key"
      >
      <div v-if="entry.type === 'divider'" class="flex items-center justify-center py-1">
        <div
          class="px-3 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-600"
        >
          {{ entry.label }}
        </div>
      </div>

      <div
        v-else
        class="flex"
        :class="entry.message.user_id === currentUserId ? 'justify-end' : 'justify-start'"
      >
        <div class="group relative max-w-[70%]">
          <div
            class="max-w-full rounded-2xl px-3 py-2 shadow-sm"
            :class="bubbleClass(entry.message)"
          >
          <img
            v-if="entry.message.type === 'image'"
            :src="entry.message.body"
            alt="Imagem enviada"
            class="max-h-64 rounded-lg object-cover"
          />
          <div v-else class="text-sm break-words">{{ entry.message.body }}</div>
          <div
            class="text-[11px] mt-1 text-right"
            :class="entry.message.user_id === currentUserId ? 'text-indigo-100' : 'text-gray-400'"
          >
            {{ formatTime(entry.message.created_at) }}
            <span
              v-if="entry.message.user_id === currentUserId"
              class="ml-1"
            >
              {{ entry.message.is_seen ? '• visto' : '• enviada' }}
            </span>
          </div>
          </div>
          <div
            v-if="canManageMessage(entry.message)"
            class="absolute -top-2 right-0 hidden group-hover:flex items-center gap-1 bg-white border border-gray-200 rounded-md shadow-sm p-1"
          >
            <button
              type="button"
              class="text-xs text-gray-600 hover:text-indigo-600"
              @click="emit('edit-message', entry.message)"
            >
              Editar
            </button>
            <button
              type="button"
              class="text-xs text-gray-600 hover:text-red-600"
              @click="emit('delete-message', entry.message)"
            >
              Apagar
            </button>
          </div>
        </div>
      </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({
  messages: { type: Array, required: true },
  currentUserId: { type: Number, required: true },
  isAdmin: { type: Boolean, default: false },
});
const emit = defineEmits(['edit-message', 'delete-message']);

const messagesEl = ref(null);
const groupedEntries = computed(() => {
  const entries = [];
  let previousDateKey = '';

  for (const message of props.messages) {
    const currentDateKey = getDateKey(message.created_at);
    if (currentDateKey !== previousDateKey) {
      entries.push({
        type: 'divider',
        key: `d-${currentDateKey}-${message.id}`,
        label: formatDateLabel(message.created_at),
      });
      previousDateKey = currentDateKey;
    }

    entries.push({
      type: 'message',
      key: `m-${message.id}`,
      message,
    });
  }

  return entries;
});

watch(
  () => props.messages.length,
  async () => {
    await nextTick();
    const el = messagesEl.value;
    if (el && shouldAutoScroll(el)) {
      el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    }
  }
);

function shouldAutoScroll(el) {
  const distanceToBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
  return distanceToBottom <= 120;
}

function formatTime(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  if (Date.now() - date.getTime() < 60 * 1000) return 'Now';
  return new Intl.DateTimeFormat('pt-PT', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function bubbleClass(message) {
  const isOwn = Number(message?.user_id) === Number(props.currentUserId);
  if (isOwn) {
    return 'bg-indigo-500 text-white rounded-br-md';
  }

  const isUnreadIncoming = !message?.is_seen;
  return isUnreadIncoming
    ? 'bg-indigo-100 text-indigo-900 rounded-bl-md chat-bubble-new'
    : 'bg-white text-gray-800 rounded-bl-md';
}

function formatDateLabel(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';

  if (isSameDay(date, new Date())) return 'Hoje';

  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  if (isSameDay(date, yesterday)) return 'Ontem';

  return new Intl.DateTimeFormat('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date);
}

function getDateKey(value) {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'invalid-date';
  return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
}

function isSameDay(a, b) {
  return (
    a.getFullYear() === b.getFullYear()
    && a.getMonth() === b.getMonth()
    && a.getDate() === b.getDate()
  );
}

function canManageMessage(message) {
  if (!message) return false;
  return Number(message.user_id) === Number(props.currentUserId) || props.isAdmin;
}
</script>

<style scoped>
.message-fade-enter-active,
.message-fade-leave-active {
  transition: all 0.2s ease;
}

.message-fade-enter-from,
.message-fade-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

.chat-bubble-new {
  animation: chat-bubble-highlight 0.3s ease;
}

@keyframes chat-bubble-highlight {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
