<template>
  <section class="flex-1 min-h-0 flex flex-col bg-white">
    <header class="px-4 py-3 border-b border-gray-200 flex items-center gap-3">
      <button
        v-if="showSidebarToggle"
        type="button"
        class="md:hidden rounded-md border border-gray-300 px-2 py-1 text-gray-600"
        @click="$emit('toggle-sidebar')"
      >
        ☰
      </button>
      <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold">
        <img
          v-if="avatarUrl"
          :src="avatarUrl"
          :alt="title"
          class="w-full h-full rounded-full object-cover"
        />
        <span v-else>{{ avatarText }}</span>
      </div>
      <div class="min-w-0">
        <div class="font-semibold text-gray-900 truncate">{{ title }}</div>
        <div class="text-xs flex items-center gap-1.5" :class="statusOnline ? 'text-green-600' : 'text-gray-500'">
          <span class="w-2 h-2 rounded-full" :class="statusOnline ? 'bg-green-500' : 'bg-gray-400'"></span>
          {{ statusLabel }}
        </div>
        <div v-if="typingLabel" class="text-xs text-indigo-600 mt-0.5">
          {{ typingLabel }}
        </div>
      </div>
      <div class="ml-auto">
        <button
          v-if="showRoomSettings"
          type="button"
          class="rounded-md border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-50"
          @click="$emit('open-room-settings')"
          aria-label="Configurar sala"
        >
          ⚙️
        </button>
      </div>
    </header>

    <MessageList
      :messages="messages"
      :current-user-id="currentUserId"
      :is-admin="isAdmin"
      @edit-message="$emit('edit-message', $event)"
      @delete-message="$emit('delete-message', $event)"
    />
    <MessageInput
      :can-send="canSend"
      @send="$emit('send', $event)"
      @typing="$emit('typing')"
      @upload-image="$emit('upload-image', $event)"
    />
  </section>
</template>

<script setup>
import { computed } from 'vue';
import MessageInput from './MessageInput.vue';
import MessageList from './MessageList.vue';

const props = defineProps({
  title: { type: String, default: 'Chat Interno' },
  statusLabel: { type: String, default: 'offline' },
  statusOnline: { type: Boolean, default: false },
  showSidebarToggle: { type: Boolean, default: false },
  avatarUrl: { type: String, default: '' },
  typingLabel: { type: String, default: '' },
  messages: { type: Array, required: true },
  canSend: { type: Boolean, default: false },
  currentUserId: { type: Number, required: true },
  showRoomSettings: { type: Boolean, default: false },
  isAdmin: { type: Boolean, default: false },
});

defineEmits(['send', 'typing', 'upload-image', 'toggle-sidebar', 'open-room-settings', 'edit-message', 'delete-message']);

const avatarText = computed(() => {
  if (!props.title) return '?';
  return String(props.title)
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');
});
</script>