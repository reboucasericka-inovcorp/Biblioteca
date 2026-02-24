<template>
  <div class="card bg-base-100 shadow">
    <div class="card-body p-6">
      <h3 class="card-title text-lg">📥 Sugestões pendentes</h3>
      <p class="text-sm text-base-content/70">
        Aprove ou rejeite as sugestões de aquisição dos cidadãos.
      </p>

      <div v-if="loading" class="py-8 text-center text-base-content/60">
        A carregar...
      </div>
      <div v-else-if="suggestions.length === 0" class="py-8 text-center text-base-content/60 text-sm">
        Nenhuma sugestão pendente.
      </div>
      <div v-else class="mt-4 space-y-4">
        <div
          v-for="s in suggestions"
          :key="s.id"
          class="flex gap-4 p-4 rounded-lg border border-base-300 bg-base-200/50"
        >
          <div class="flex-shrink-0">
            <img
              v-if="s.thumbnail_url"
              :src="s.thumbnail_url"
              :alt="s.title"
              class="h-20 w-14 object-cover rounded"
            />
            <div v-else class="h-20 w-14 rounded bg-base-300 flex items-center justify-center text-base-content/50 text-xs">
              Sem capa
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <h5 class="font-medium">{{ s.title }}</h5>
            <p v-if="(s.authors || []).length" class="text-sm text-base-content/70 mt-1">
              {{ (s.authors || []).join(', ') }}
            </p>
            <p v-if="s.user" class="text-xs text-base-content/60 mt-1">
              Sugerido por: {{ s.user.name }} ({{ s.user.email }})
            </p>
            <p class="text-xs text-base-content/50 mt-1">
              {{ formatDate(s.created_at) }}
            </p>
          </div>
          <div class="flex-shrink-0 flex gap-2">
            <button
              type="button"
              class="btn btn-sm btn-success"
              :disabled="actionId === s.id"
              @click="approve(s)"
            >
              <span v-if="actionId === s.id">A processar...</span>
              <span v-else>Aprovar</span>
            </button>
            <button
              type="button"
              class="btn btn-sm btn-error btn-outline"
              :disabled="actionId === s.id"
              @click="reject(s)"
            >
              Rejeitar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { unwrapPage } from '../api';

const suggestions = ref([]);
const loading = ref(false);
const actionId = ref(null);

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/book-suggestions', {
      params: { status: 'pending' },
    });
    const page = unwrapPage(res);
    suggestions.value = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
  } catch (e) {
    console.error('Load suggestions error:', e);
    suggestions.value = [];
  } finally {
    loading.value = false;
  }
}

async function approve(s) {
  actionId.value = s.id;
  try {
    await window.axios.patch(`/api/book-suggestions/${s.id}/approve`);
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao aprovar.';
    alert(msg);
  } finally {
    actionId.value = null;
  }
}

async function reject(s) {
  actionId.value = s.id;
  try {
    await window.axios.patch(`/api/book-suggestions/${s.id}/reject`);
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao rejeitar.';
    alert(msg);
  } finally {
    actionId.value = null;
  }
}

function formatDate(val) {
  if (!val) return '-';
  return new Date(val).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
}

onMounted(() => {
  load();
  window.addEventListener('suggestions-refresh', load);
});

onUnmounted(() => {
  window.removeEventListener('suggestions-refresh', load);
});
</script>
