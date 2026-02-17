<template>
  <div>
    <!-- Indicadores no topo -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white border rounded-lg p-4 shadow-sm">
        <p class="text-sm text-gray-500">Total</p>
        <p class="text-2xl font-bold">{{ stats.total }}</p>
      </div>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 shadow-sm">
        <p class="text-sm text-blue-600">Ativas</p>
        <p class="text-2xl font-bold text-blue-700">{{ stats.active }}</p>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-lg p-4 shadow-sm">
        <p class="text-sm text-green-600">Devolvidas</p>
        <p class="text-2xl font-bold text-green-700">{{ stats.returned }}</p>
      </div>
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 shadow-sm">
        <p class="text-sm text-amber-600">Atrasadas</p>
        <p class="text-2xl font-bold text-amber-700">{{ stats.late }}</p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200 mb-6">
      <select
        v-model="status"
        class="border border-gray-300 rounded-md px-3 py-2 min-w-[140px] bg-white"
      >
        <option value="">Todos os status</option>
        <option value="active">Ativas</option>
        <option value="returned">Devolvidas</option>
        <option value="late">Atrasadas</option>
      </select>
      <select
        v-model="sort"
        class="border border-gray-300 rounded-md px-3 py-2 min-w-[120px] bg-white"
      >
        <option value="created_at">Data</option>
        <option value="request_date">Data requisição</option>
        <option value="due_date">Data devolução</option>
        <option value="status">Status</option>
      </select>
      <select
        v-model="dir"
        class="border border-gray-300 rounded-md px-3 py-2 min-w-[100px] bg-white"
      >
        <option value="desc">Desc</option>
        <option value="asc">Asc</option>
      </select>
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto">
      <table class="table-auto w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3 text-left">Nº</th>
            <th class="p-3 text-left">Utilizador</th>
            <th class="p-3 text-left">Livro</th>
            <th class="p-3 text-left">Data requisição</th>
            <th class="p-3 text-left">Data devolução</th>
            <th class="p-3 text-left">Status</th>
            <th v-if="userIsAdmin" class="p-3 text-left">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in requisitions" :key="r.id" class="hover:bg-gray-50">
            <td class="p-3 font-mono font-medium">{{ r.sequential_number }}</td>
            <td class="p-3">{{ r.user?.name ?? '-' }}</td>
            <td class="p-3">
              <a
                v-if="r.book"
                :href="`/books/${r.book.id}`"
                class="text-blue-600 hover:underline"
              >
                {{ r.book.name }}
              </a>
              <span v-else>-</span>
            </td>
            <td class="p-3 text-sm">{{ formatDate(r.request_date) }}</td>
            <td class="p-3 text-sm">{{ formatDate(r.due_date) }}</td>
            <td class="p-3">
              <span
                :class="{
                  'bg-blue-100 text-blue-800': r.status === 'active',
                  'bg-green-100 text-green-800': r.status === 'returned',
                  'bg-amber-100 text-amber-800': r.status === 'late',
                }"
                class="px-2 py-1 text-xs font-medium rounded"
              >
                {{ statusLabel(r.status) }}
              </span>
            </td>
            <td v-if="userIsAdmin" class="p-3">
              <button
                v-if="r.status === 'active'"
                @click="confirmReturn(r.id)"
                :disabled="confirmingId === r.id"
                class="btn btn-sm btn-primary"
              >
                {{ confirmingId === r.id ? '...' : 'Confirmar devolução' }}
              </button>
            </td>
          </tr>
          <tr v-if="requisitions.length === 0">
            <td :colspan="userIsAdmin ? 7 : 6" class="p-6 text-center text-gray-500">
              Nenhuma requisição encontrada.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div
      v-if="pagination.last_page > 1"
      class="mt-4 flex items-center justify-between"
    >
      <p class="text-sm text-gray-600">
        Página {{ pagination.current_page }} de {{ pagination.last_page }}
        ({{ pagination.total }} registos)
      </p>
      <div class="flex gap-2">
        <button
          :disabled="pagination.current_page <= 1"
          @click="goToPage(pagination.current_page - 1)"
          class="px-3 py-1 border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
        >
          Anterior
        </button>
        <button
          :disabled="pagination.current_page >= pagination.last_page"
          @click="goToPage(pagination.current_page + 1)"
          class="px-3 py-1 border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
        >
          Próxima
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

defineProps({
  userIsAdmin: { type: Boolean, default: false },
});

const requisitions = ref([]);
const confirmingId = ref(null);
const stats = ref({ total: 0, active: 0, returned: 0, late: 0 });
const status = ref('');
const sort = ref('created_at');
const dir = ref('desc');
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

async function loadStats() {
  const res = await window.axios.get('/api/requisitions/stats');
  stats.value = res.data;
}

async function load() {
  const params = new URLSearchParams({
    page: pagination.value.current_page,
    sort: sort.value,
    dir: dir.value,
  });
  if (status.value) params.set('status', status.value);

  const res = await window.axios.get(`/api/requisitions?${params.toString()}`);
  requisitions.value = res.data.data;
  pagination.value = {
    current_page: res.data.current_page,
    last_page: res.data.last_page,
    total: res.data.total,
  };
}

function goToPage(page) {
  pagination.value.current_page = page;
  load();
}

function formatDate(val) {
  if (!val) return '-';
  const d = new Date(val);
  return d.toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
}

function statusLabel(s) {
  const labels = { active: 'Ativa', returned: 'Devolvida', late: 'Atrasada' };
  return labels[s] ?? s;
}

async function confirmReturn(requisitionId) {
  confirmingId.value = requisitionId;
  try {
    await window.axios.post(`/api/requisitions/${requisitionId}/return`);
    await loadStats();
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao confirmar devolução.';
    alert(msg);
  } finally {
    confirmingId.value = null;
  }
}

watch([status, sort, dir], () => {
  pagination.value.current_page = 1;
  load();
});

onMounted(() => {
  loadStats();
  load();
});
</script>
