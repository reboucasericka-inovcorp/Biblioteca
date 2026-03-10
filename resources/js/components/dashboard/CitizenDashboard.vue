<template>
  <div class="space-y-8">
    <!-- Pesquisar Google Books -->
    <section>
      <google-books-search :user-is-admin="false" @suggested="loadSuggestions" @requisitioned="loadRequisitions" />
    </section>

    <!-- Minhas sugestões -->
    <section class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <h3 class="card-title text-lg">📨 Meus pedidos</h3>
        <p class="text-sm text-base-content/70">Pedidos solicitados.</p>

        <div v-if="suggestionsLoading" class="py-8 text-center text-base-content/60">
          A carregar...
        </div>
        <div v-else-if="suggestions.length === 0" class="py-8 text-center text-base-content/60 text-sm">
          Ainda não enviou sugestões.
        </div>
        <div v-else class="overflow-x-auto mt-4">
          <table class="table table-zebra table-sm">
            <thead>
              <tr>
                <th>Capa</th>
                <th>Título</th>
                <th>Autores</th>
                <th>Status</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in suggestions" :key="s.id">
                <td>
                  <img
                    v-if="s.thumbnail_url"
                    :src="s.thumbnail_url"
                    :alt="s.title"
                    class="h-12 w-8 object-cover rounded"
                  />
                  <div v-else class="h-12 w-8 rounded bg-base-300" />
                </td>
                <td class="font-medium">{{ s.title }}</td>
                <td class="text-sm">{{ (s.authors || []).join(', ') || '-' }}</td>
                <td>
                  <span
                    class="badge badge-sm"
                    :class="{
                      'badge-warning': s.status === 'pending',
                      'badge-success': s.status === 'approved',
                      'badge-error': s.status === 'rejected',
                    }"
                  >
                    {{ statusLabel(s.status) }}
                  </span>
                </td>
                <td class="text-sm">{{ formatDate(s.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Minhas requisições -->
    <section>
      <h3 class="text-lg font-semibold mb-4">📚 Minhas requisições</h3>
      <requisitions-table :user-is-admin="false" />
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { unwrapPage } from '../../api';

const suggestions = ref([]);
const suggestionsLoading = ref(false);

async function loadSuggestions() {
  suggestionsLoading.value = true;
  try {
    const res = await window.axios.get('/api/book-suggestions');
    const page = unwrapPage(res);
    suggestions.value = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
  } catch (e) {
    console.error('Load suggestions error:', e);
    suggestions.value = [];
  } finally {
    suggestionsLoading.value = false;
  }
}

function loadRequisitions() {
  window.dispatchEvent(new CustomEvent('requisitions-refresh'));
}

function statusLabel(s) {
  const labels = { pending: 'Pendente', approved: 'Aprovada', rejected: 'Rejeitada' };
  return labels[s] ?? s;
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
  loadSuggestions();
});
</script>
