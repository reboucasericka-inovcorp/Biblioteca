<template>
  <div class="space-y-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
          <div>
            <h3 class="text-lg font-semibold text-base-content">📥 Sugestões pendentes</h3>
            <p class="text-sm text-base-content/70">Aprove ou rejeite as sugestões de aquisição dos cidadãos.</p>
          </div>
        </div>

        <div v-if="loading" class="py-8 text-center text-base-content/60">
          A carregar...
        </div>

        <div v-else>
          <div class="overflow-x-auto">
            <table class="table table-zebra table-lg w-full">
              <thead>
                <tr>
                  <th class="min-w-[3rem] whitespace-nowrap">Capa</th>
                  <th class="min-w-[12rem] whitespace-nowrap">Título</th>
                  <th class="min-w-[14rem] whitespace-nowrap">Autor</th>
                  <th class="min-w-[12rem] whitespace-nowrap">Utilizador</th>
                  <th class="min-w-[10rem] whitespace-nowrap">Data</th>
                  <th class="min-w-[8rem] whitespace-nowrap">Status</th>
                  <th class="min-w-[10rem] whitespace-nowrap">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in suggestions" :key="s.id">
                  <td class="p-4">
                    <img
                      v-if="s.thumbnail_url"
                      :src="s.thumbnail_url"
                      :alt="s.title"
                      class="w-10 h-14 object-cover rounded"
                    />
                    <div
                      v-else
                      class="w-10 h-14 rounded bg-base-300 flex items-center justify-center text-base-content/50 text-xs"
                    >
                      Sem capa
                    </div>
                  </td>
                  <td class="p-4 text-sm font-medium whitespace-nowrap">{{ s.title ?? '-' }}</td>
                  <td class="p-4 text-sm whitespace-nowrap">
                    {{ (s.authors || []).join(', ') || '-' }}
                  </td>
                  <td class="p-4 text-sm whitespace-nowrap">
                    <div class="flex flex-col">
                      <span class="leading-5">{{ s.user?.name ?? '-' }}</span>
                      <span class="text-xs text-base-content/60 leading-4 truncate">{{ s.user?.email ?? '' }}</span>
                    </div>
                  </td>
                  <td class="p-4 text-sm whitespace-nowrap">{{ formatDate(s.created_at) }}</td>
                  <td class="p-4 whitespace-nowrap">
                    <span class="badge badge-sm" :class="statusBadgeClass(s.status)">
                      {{ statusLabel(s.status) }}
                    </span>
                  </td>
                  <td class="p-4 whitespace-nowrap">
                    <div class="flex flex-wrap gap-2">
                      <button
                        v-if="s.status === 'pending'"
                        type="button"
                        class="btn btn-sm btn-success"
                        :disabled="actionLoading || actionId === s.id"
                        @click="approve(s)"
                      >
                        {{ actionId === s.id ? 'A processar...' : 'Aprovar' }}
                      </button>
                      <button
                        v-if="s.status === 'pending'"
                        type="button"
                        class="btn btn-sm btn-error"
                        :disabled="actionLoading || actionId === s.id"
                        @click="reject(s)"
                      >
                        {{ actionId === s.id ? 'A processar...' : 'Rejeitar' }}
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="suggestions.length === 0">
                  <td colspan="7" class="p-6 text-center text-sm text-base-content/60">
                    Sem sugestões pendentes no momento.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            v-if="pagination.last_page > 1"
            class="mt-4 flex items-center justify-between"
          >
            <p class="text-sm text-base-content/70">
              Página {{ pagination.current_page }} de {{ pagination.last_page }}
              ({{ pagination.total }} registos)
            </p>
            <div class="flex gap-4">
              <button
                :disabled="pagination.current_page <= 1"
                @click="goToPage(pagination.current_page - 1)"
                class="btn btn-sm btn-ghost disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Anterior
              </button>
              <button
                :disabled="pagination.current_page >= pagination.last_page"
                @click="goToPage(pagination.current_page + 1)"
                class="btn btn-sm btn-ghost disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Próxima
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { unwrapPage } from '../../api';

const suggestions = ref([]);
const loading = ref(false);
const actionId = ref(null);
const actionLoading = ref(false);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/book-suggestions', {
      params: { status: 'pending', page: pagination.value.current_page },
    });
    const page = unwrapPage(res);
    suggestions.value = Array.isArray(page?.data)
      ? page.data
      : (Array.isArray(page) ? page : []);
    pagination.value = {
      current_page: page.current_page ?? 1,
      last_page: page.last_page ?? 1,
      total: page.total ?? 0,
    };
  } catch (e) {
    console.error('Load suggestions error:', e);
    suggestions.value = [];
    pagination.value = { current_page: 1, last_page: 1, total: 0 };
  } finally {
    loading.value = false;
  }
}

async function approve(s) {
  actionId.value = s.id;
  if (actionLoading.value) return;
  actionLoading.value = true;
  try {
    await window.axios.patch(`/api/book-suggestions/${s.id}/approve`);
    window.showToast('Sugestão aprovada com sucesso.', 'success');
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao aprovar.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
    actionLoading.value = false;
  }
}

async function reject(s) {
  actionId.value = s.id;
  if (actionLoading.value) return;
  actionLoading.value = true;
  try {
    await window.axios.patch(`/api/book-suggestions/${s.id}/reject`);
    window.showToast('Sugestão rejeitada com sucesso.', 'success');
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao rejeitar.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
    actionLoading.value = false;
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

function statusLabel(s) {
  const labels = {
    pending: 'Pendente',
    approved: 'Aprovada',
    rejected: 'Rejeitada',
  };
  return labels[s] ?? s;
}

function statusBadgeClass(s) {
  const map = {
    pending: 'badge-warning',
    approved: 'badge-success',
    rejected: 'badge-error',
  };
  return map[s] ?? 'badge-ghost';
}

function goToPage(page) {
  pagination.value.current_page = page;
  load();
}

onMounted(() => {
  load();
  window.addEventListener('suggestions-refresh', load);
});

onUnmounted(() => {
  window.removeEventListener('suggestions-refresh', load);
});
</script>
