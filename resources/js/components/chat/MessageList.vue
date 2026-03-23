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
        <div
          class="max-w-[70%] rounded-2xl px-3 py-2 shadow-sm"
          :class="entry.message.user_id === currentUserId ? 'bg-indigo-500 text-white rounded-br-md' : 'bg-white text-gray-800 rounded-bl-md'"
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
});

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
  return new Intl.DateTimeFormat('pt-PT', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
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
</style>
