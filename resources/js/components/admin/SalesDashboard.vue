<template>
  <div class="space-y-6">
    <h3 class="text-lg font-semibold text-base-content">Dashboard de vendas</h3>
    <div v-if="loading" class="text-sm text-base-content/60">A carregar...</div>
    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="card bg-base-100 shadow border border-base-300">
          <div class="card-body p-8">
            <p class="text-sm text-base-content/60">Total vendas (pagas)</p>
            <p class="text-2xl font-bold">{{ formatPrice(stats.total_sales) }}</p>
          </div>
        </div>
        <div class="card bg-base-100 shadow border border-base-300">
          <div class="card-body p-8">
            <p class="text-sm text-base-content/60">Pedidos hoje</p>
            <p class="text-2xl font-bold">{{ stats.orders_today }}</p>
          </div>
        </div>
        <div class="card bg-base-100 shadow border border-base-300">
          <div class="card-body p-8">
            <p class="text-sm text-base-content/60">Livros mais vendidos</p>
            <p class="text-2xl font-bold">{{ stats.top_books?.length ?? 0 }} no top 10</p>
          </div>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body p-8">
          <h4 class="font-semibold mb-3">Top 10 livros mais vendidos</h4>
          <div class="overflow-x-auto">
            <table class="table table-zebra table-lg table-sm w-full">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Livro</th>
                  <th class="text-right">Unidades vendidas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in (stats.top_books || [])" :key="row.book_id">
                  <td class="text-base-content/60">{{ i + 1 }}</td>
                  <td class="font-medium">{{ row.book_title || '—' }}</td>
                  <td class="text-right">{{ row.total_quantity }}</td>
                </tr>
                <tr v-if="!stats.top_books?.length">
                  <td colspan="3" class="text-center text-base-content/60 py-4">Nenhuma venda registada.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { unwrap } from '../../api';

const loading = ref(true);
const stats = ref({
  total_sales: 0,
  orders_today: 0,
  top_books: [],
});

function formatPrice(value) {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number(value));
}

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/orders/stats');
    const data = unwrap(res);
    stats.value = data || stats.value;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  load();
});
</script>
