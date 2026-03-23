<template>
  <div
    v-if="open"
    class="fixed inset-0 z-[1100] bg-black/40 flex items-center justify-center p-4"
    @click.self="close"
  >
    <div class="w-full max-w-sm rounded-xl bg-white shadow-2xl border border-gray-100 p-4 space-y-3">
      <h3 class="font-semibold text-lg text-gray-900">Nova sala</h3>

      <input
        v-model="name"
        type="text"
        class="w-full border border-gray-300 rounded-lg p-2 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        placeholder="Nome da sala"
      />

      <label class="text-sm font-medium text-gray-600">Convidar utilizadores</label>
      <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
        <label
          v-for="user in users"
          :key="user.id"
          class="flex items-center gap-2 p-1 rounded hover:bg-gray-50 text-sm text-gray-700"
        >
          <input v-model="selectedIds" type="checkbox" :value="user.id" class="accent-indigo-600" />
          <span>{{ user.name }} ({{ user.email }})</span>
        </label>
      </div>

      <div class="flex justify-end gap-2 pt-1">
        <button
          type="button"
          class="px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
          @click="close"
        >
          Cancelar
        </button>
        <button
          type="button"
          class="px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60"
          :disabled="!name.trim()"
          @click="create"
        >
          Criar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  users: { type: Array, required: true },
});

const emit = defineEmits(['close', 'create']);
const name = ref('');
const selectedIds = ref([]);

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      name.value = '';
      selectedIds.value = [];
    }
  }
);

function close() {
  emit('close');
}

function create() {
  const roomName = name.value.trim();
  if (!roomName) return;
  emit('create', { name: roomName, user_ids: selectedIds.value });
  close();
}
</script>
