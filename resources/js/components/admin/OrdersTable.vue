<template>
  <div class="space-y-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body p-8">
        <div class="flex flex-wrap items-center gap-4">
          <select v-model="status" class="select select-bordered h-10 min-w-[140px] bg-base-100">
            <option value="">Todos</option>
            <option value="pending">Pendentes</option>
            <option value="paid">Pagas</option>
            <option value="cancelled">Canceladas</option>
          </select>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 shadow">
      <div class="card-body p-8">
        <div class="overflow-x-auto">
          <table class="table table-zebra table-lg w-full">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th class="text-right">Total</th>
                <th>Estado</th>
                <th>Data</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td class="font-mono text-sm">{{ order.id }}</td>
                <td class="text-sm">{{ order.customer_name ?? '—' }}</td>
                <td class="text-right font-medium">{{ formatPrice(order.total) }}</td>
                <td>
                  <span class="badge badge-sm" :class="badgeClass(order.status)">
                    {{ statusLabel(order.status) }}
                  </span>
                </td>
                <td class="text-sm text-base-content/70">{{ formatDate(order.created_at) }}</td>
                <td>
                  <a :href="`/orders/${order.id}`" class="link link-primary text-sm">Detalhe</a>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="6" class="text-center text-sm text-base-content/60 py-6">
                  Nenhuma encomenda encontrada.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-4">
          <button
            class="btn btn-sm btn-ghost"
            :disabled="pagination.current_page <= 1"
            @click="goToPage(pagination.current_page - 1)"
          >
            ←
          </button>
          <span class="flex items-center px-3 text-sm">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
          </span>
          <button
            class="btn btn-sm btn-ghost"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"
          >
            →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { unwrapPage } from '../../api';

const orders = ref([]);
const status = ref('');
const pagination = ref({
  current_page: 1,
  last_page: 1,
});

async function load() {
  const res = await window.axios.get('/api/orders', {
    params: {
      status: status.value || undefined,
      page: pagination.value.current_page,
    },
  });

  const pageData = unwrapPage(res);
  orders.value = pageData.data ?? [];
  pagination.value.current_page = pageData.current_page ?? 1;
  pagination.value.last_page = pageData.last_page ?? 1;
}

function goToPage(page) {
  pagination.value.current_page = page;
  load();
}

function formatPrice(value) {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number(value));
}

function formatDate(value) {
  if (!value) return '—';
  const d = new Date(value);
  return d.toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function statusLabel(s) {
  const map = { pending: 'Pendente', paid: 'Pago', cancelled: 'Cancelada' };
  return map[s] ?? s;
}

function badgeClass(s) {
  return {
    'badge-warning': s === 'pending',
    'badge-success': s === 'paid',
    'badge-error': s === 'cancelled',
  };
}

watch(status, () => {
  pagination.value.current_page = 1;
  load();
});

onMounted(() => {
  load();
});
</script>
