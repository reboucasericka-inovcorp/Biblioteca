<template>
  <aside
    class="w-80 shrink-0 border-r bg-white flex flex-col min-h-0 fixed md:static inset-y-0 left-0 z-30 transform transition-transform duration-200"
    :class="isMobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
  >
    <div class="p-4 border-b">
      <div class="font-semibold text-gray-900 text-lg">Conversas</div>
      <button
        v-if="isAdmin"
        class="mt-3 w-full rounded-lg bg-indigo-600 text-white py-2 hover:bg-indigo-700 transition-colors"
        @click="$emit('open-room-modal')"
      >
        + Nova Sala
      </button>
    </div>

    <div class="p-3 border-b">
      <input
        :value="search"
        type="text"
        placeholder="Buscar..."
        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
        @input="$emit('update:search', $event.target?.value ?? '')"
      />
    </div>

    <div class="flex-1 overflow-y-auto">
      <button
        v-for="item in conversations"
        :key="item.key"
        class="w-full text-left px-3 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors"
        :class="item.key === activeKey ? 'bg-indigo-50' : ''"
        @click="selectConversation(item)"
      >
        <div class="flex items-start gap-3">
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-semibold"
            :class="item.type === 'room' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700'"
          >
            <img
              v-if="item.avatarUrl"
              :src="item.avatarUrl"
              :alt="item.name"
              class="w-full h-full rounded-full object-cover"
            />
            <span v-else>{{ item.avatarText }}</span>
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <div class="font-medium text-gray-900 truncate flex items-center gap-2">
                <span>{{ item.name }}</span>
                <span
                  v-if="item.type === 'direct'"
                  class="w-2 h-2 rounded-full"
                  :class="item.status === 'online' ? 'bg-green-500' : 'bg-gray-300'"
                ></span>
              </div>
              <div class="text-xs text-gray-400 shrink-0">{{ item.timeLabel }}</div>
            </div>
            <div class="flex items-center justify-between gap-2 mt-0.5">
              <div class="text-sm text-gray-500 truncate">{{ item.preview }}</div>
              <div class="flex items-center gap-1">
                <span
                  v-if="item.unreadCount > 0"
                  class="inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-xs"
                >
                  {{ item.unreadCount }}
                </span>
                <button
                  type="button"
                  class="text-gray-400 hover:text-red-500 text-xs px-1"
                  @click.stop="$emit('remove-conversation', item)"
                  aria-label="Remover conversa"
                >
                  🗑️
                </button>
              </div>
            </div>
          </div>
        </div>
      </button>
    </div>
  </aside>
</template>

<script setup>
defineProps({
  conversations: { type: Array, required: true },
  search: { type: String, default: '' },
  isAdmin: { type: Boolean, default: false },
  activeKey: { type: String, default: '' },
  isMobileOpen: { type: Boolean, default: false },
});

const emit = defineEmits(['update:search', 'select-conversation', 'open-room-modal', 'close-sidebar', 'remove-conversation']);

function selectConversation(item) {
  emit('select-conversation', item);
  emit('close-sidebar');
}
</script>