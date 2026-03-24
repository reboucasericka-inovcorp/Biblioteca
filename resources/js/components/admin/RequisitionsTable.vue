<template>
   <div class="space-y-4">
    <!-- Indicadores no topo -->
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Pendentes de aprovação -->
      <div class="card bg-base-100 shadow-md">
        <div class="card-body p-6">
          <p class="text-sm text-base-content/70">Pendentes de aprovação</p>
          <p class="text-2xl font-bold text-warning">{{ stats.pending ?? 0 }}</p>
        </div>
      </div>
      <!-- Requisições Ativas -->
      <div class="card bg-base-100 shadow-md">
        <div class="card-body p-6">
          <p class="text-sm text-base-content/70">Requisições Ativas</p>
          <p class="text-2xl font-bold text-info">{{ stats.active }}</p>
        </div>
      </div>
       <!-- Requisições nos últimos 30 dias -->
      <div class="card bg-base-100 shadow-md">
        <div class="card-body p-6">
          <p class="text-sm text-base-content/70">Requisições nos últimos 30 dias</p>
          <p class="text-2xl font-bold text-success">{{ stats.last_30_days }}</p>
        </div>
      </div>

      <!-- Livros entregues Hoje -->
      <div class="card bg-base-100 shadow-md">
        <div class="card-body p-6">
          <p class="text-sm text-base-content/70">Livros entregues Hoje</p>
          <p class="text-2xl font-bold text-secondary">{{ stats.delivered_today }}</p>
        </div>
      </div>
    </div>



    

    <!-- Filtros -->
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <div class="flex flex-wrap items-center gap-4">
      <select
        v-model="status"
        class="select select-bordered min-w-[140px] bg-base-100"
      >
        <option value="">Todos os status</option>
        <option value="pending">Pendentes</option>
        <option value="active">Ativas</option>
        <option value="late">Atrasadas</option>
      </select>
      <select
        v-model="sort"
        class="select select-bordered min-w-[120px] bg-base-100"
      >
        <option value="created_at">Data</option>
        <option value="request_date">Data requisição</option>
        <option value="due_date">Data devolução</option>
        <option value="status">Status</option>
      </select>
      <select
        v-model="dir"
        class="select select-bordered min-w-[100px] bg-base-100"
      >
        <option value="desc">Desc</option>
        <option value="asc">Asc</option>
      </select>
        </div>
      </div>
    </div>

    <!-- Tabela -->
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
    <div class="overflow-x-auto">
      <table class="table table-zebra table-lg w-full">
        <thead>
          <tr>
            <th class="whitespace-nowrap min-w-[6rem]">Nº</th>
            <th v-if="userIsAdmin" class="whitespace-nowrap min-w-[8rem]">Utilizador</th>
            <th class="min-w-[10rem]">Livro</th>
            <th class="whitespace-nowrap min-w-[6rem]">Data requisição</th>
            <th class="whitespace-nowrap min-w-[6rem]">Data devolução</th>
            <th class="whitespace-nowrap min-w-[6rem]">Dias decorridos</th>
            <th class="whitespace-nowrap min-w-[5rem]">Status</th>
            <th v-if="userIsAdmin" class="whitespace-nowrap min-w-[10rem]">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in requisitions" :key="r.id">
            <td class="p-4 font-mono text-sm font-medium whitespace-nowrap">{{ r.sequential_number }}</td>
            <td v-if="userIsAdmin" class="p-4 text-sm whitespace-nowrap">
              <a
                v-if="r.user"
                :href="`/users/${r.user.id}`"
                class="link link-primary"
              >
                {{ r.user.name }}
              </a>
              <span v-else>-</span>
            </td>
            <td class="p-4 text-sm min-w-[10rem]">
              <a
                v-if="r.book"
                :href="`/books/${r.book.id}`"
                class="link link-primary"
              >
                {{ r.book.title ?? r.book.name ?? '-' }}
              </a>
              <span v-else>-</span>
            </td>
            <td class="p-4 text-sm whitespace-nowrap">{{ formatDate(r.request_date) }}</td>
            <td class="p-4 text-sm whitespace-nowrap">{{ formatDate(r.due_date) }}</td>
            <td class="p-4 text-sm whitespace-nowrap">{{ r.days_elapsed ?? '-' }}</td>
            <td class="p-4 whitespace-nowrap">
              <span
                class="badge badge-sm"
                :class="statusBadgeClass(r.status)"
              >
                {{ statusLabel(r.status) }}
              </span>
            </td>
            <td v-if="userIsAdmin" class="p-4 whitespace-nowrap">
              <div class="flex flex-wrap gap-2">
                <template v-if="r.status === 'pending'">
                  <button
                    type="button"
                    @click="approve(r.id)"
                    :disabled="actionId === r.id"
                    class="btn btn-sm btn-success"
                  >
                    {{ actionId === r.id ? '…' : 'Aprovar' }}
                  </button>
                  <button
                    type="button"
                    @click="reject(r.id)"
                    :disabled="actionId === r.id"
                    class="btn btn-sm btn-ghost"
                  >
                    {{ actionId === r.id ? '…' : 'Rejeitar' }}
                  </button>
                </template>
                <button
                  v-else-if="r.status === 'active' || r.status === 'late'"
                  type="button"
                  @click="confirmReturn(r.id)"
                  :disabled="actionId === r.id"
                  class="btn btn-sm btn-primary"
                >
                  {{ actionId === r.id ? '…' : 'Confirmar devolução' }}
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="requisitions.length === 0">
            <td :colspan="userIsAdmin ? 8 : 6" class="p-6 text-center text-sm text-base-content/60">
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
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { unwrap, unwrapPage } from '../../api';

defineProps({
  userIsAdmin: { type: Boolean, default: false },
});

const requisitions = ref([]);
/** Pedido em curso: aprovar, rejeitar ou confirmar devolução */
const actionId = ref(null);
const stats = ref({ active: 0, pending: 0, last_30_days: 0, delivered_today: 0 });
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
  stats.value = unwrap(res) ?? {};
}

async function load() {
  const res = await window.axios.get('/api/requisitions', {
    params: {
      page: pagination.value.current_page,
      sort: sort.value,
      dir: dir.value,
      ...(status.value && { status: status.value }),
    },
  });
  const pageData = unwrapPage(res);
  requisitions.value = pageData.data ?? [];
  pagination.value = {
    current_page: pageData.current_page ?? 1,
    last_page: pageData.last_page ?? 1,
    total: pageData.total ?? 0,
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
  const labels = {
    pending: 'Pendente',
    active: 'Ativa',
    returned: 'Devolvida',
    late: 'Atrasada',
    rejected: 'Rejeitada',
  };
  return labels[s] ?? s;
}

function statusBadgeClass(s) {
  const map = {
    pending: 'badge-warning',
    active: 'badge-primary',
    returned: 'badge-success',
    late: 'badge-error',
    rejected: 'badge-ghost',
  };
  return map[s] ?? 'badge-ghost';
}

async function approve(requisitionId) {
  actionId.value = requisitionId;
  try {
    await window.axios.patch(`/api/requisitions/${requisitionId}/approve`);
    window.showToast('Pedido aprovado.', 'success');
    await loadStats();
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao aprovar.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
  }
}

async function reject(requisitionId) {
  if (!window.confirm('Rejeitar este pedido? O cidadão verá o estado como rejeitado.')) {
    return;
  }
  actionId.value = requisitionId;
  try {
    await window.axios.patch(`/api/requisitions/${requisitionId}/reject`);
    window.showToast('Pedido rejeitado.', 'success');
    await loadStats();
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao rejeitar.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
  }
}

async function confirmReturn(requisitionId) {
  actionId.value = requisitionId;
  try {
    await window.axios.post(`/api/requisitions/${requisitionId}/return`);
    await loadStats();
    await load();
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao confirmar devolução.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
  }
}

watch([status, sort, dir], () => {
  pagination.value.current_page = 1;
  load();
});

onMounted(() => {
  loadStats();
  load();
  window.addEventListener('requisitions-refresh', handleRefresh);
});

onUnmounted(() => {
  window.removeEventListener('requisitions-refresh', handleRefresh);
});

function handleRefresh() {
  loadStats();
  load();
}
</script>
