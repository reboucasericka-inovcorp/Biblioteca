<template>
  <div
    v-if="open && room"
    class="fixed inset-0 z-[1200] bg-black/40 flex items-center justify-center p-4"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl border border-gray-100 p-4 space-y-4">
      <h3 class="font-semibold text-lg text-gray-900">Gerir sala</h3>

      <div class="grid grid-cols-1 gap-3">
        <input
          v-model="name"
          type="text"
          class="w-full border border-gray-300 rounded-lg p-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          placeholder="Nome da sala"
        />
        <input
          v-model="avatar"
          type="text"
          class="w-full border border-gray-300 rounded-lg p-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          placeholder="URL do avatar (opcional)"
        />
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Membros</label>
        <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg divide-y">
          <div
            v-for="member in room.users || []"
            :key="member.id"
            class="px-3 py-2 flex items-center justify-between text-sm"
          >
            <span>{{ member.name }} ({{ member.email }})</span>
            <button
              type="button"
              class="text-red-600 hover:underline"
              @click="$emit('remove-member', member)"
            >
              Remover
            </button>
          </div>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Convidar utilizador</label>
        <div class="mt-2 flex gap-2">
          <select
            v-model="selectedUserId"
            class="flex-1 border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option :value="0">Selecionar utilizador...</option>
            <option
              v-for="user in availableUsers"
              :key="user.id"
              :value="user.id"
            >
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <button
            type="button"
            class="px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60"
            :disabled="!selectedUserId"
            @click="inviteSelectedUser"
          >
            Convidar
          </button>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button
          type="button"
          class="px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
          @click="$emit('close')"
        >
          Fechar
        </button>
        <button
          type="button"
          class="px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700"
          @click="save"
        >
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  room: { type: Object, default: null },
  users: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'save', 'invite-member', 'remove-member']);
const name = ref('');
const avatar = ref('');
const selectedUserId = ref(0);

watch(
  () => props.room,
  (room) => {
    name.value = room?.name ?? '';
    avatar.value = room?.avatar ?? '';
    selectedUserId.value = 0;
  },
  { immediate: true }
);

const availableUsers = computed(() => {
  const memberIds = new Set((props.room?.users ?? []).map((user) => Number(user.id)));
  return props.users.filter((user) => !memberIds.has(Number(user.id)));
});

function save() {
  if (!props.room) return;
  emit('save', {
    id: props.room.id,
    name: name.value.trim(),
    avatar: avatar.value.trim() || null,
  });
}

function inviteSelectedUser() {
  if (!selectedUserId.value) return;
  emit('invite-member', Number(selectedUserId.value));
  selectedUserId.value = 0;
}
</script>
