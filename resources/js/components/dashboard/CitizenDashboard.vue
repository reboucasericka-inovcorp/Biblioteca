<template>
  <div class="pb-12">
    
    <!--Pesquisa do google api -->
    <section class="mb-10">
      <google-books-search :user-is-admin="false" />
    </section>

    <!--SEÇÕES -->
    <div class="space-y-10">

      <!--FAVORITOS -->
      <section class="space-y-4">
        <h2 class="text-xl sm:text-2xl font-bold text-base-content tracking-tight">
          Meus Favoritos ({{ favoriteBooks.length }})
        </h2>

        <!-- Loading -->
        <div v-if="favoritesStore.loading" class="rounded-xl bg-base-200/50 border border-base-300 py-12 text-center text-base-content/60">
          A carregar...
        </div>

        <!-- Vazio -->
        <div v-else-if="!favoritesStore.hasFavorites" class="rounded-xl bg-base-200/50 border border-base-300 py-12 text-center">
          <p class="text-base-content/60 text-sm">Ainda não tem livros nos favoritos.</p>
          <p class="text-base-content/50 text-xs mt-1">Use o ❤️ na página de um livro para adicionar.</p>
          <a href="/books" class="mt-3 inline-block text-primary font-medium hover:underline">
            Explorar livros
          </a>
        </div>

        <!-- Lista -->
        <books-grid
          v-else
          :books="favoriteBooks"
          :is-logged="true"
        />
      </section>

    </div>

    <!-- SUGESTÕES + REQUISIÇÕES -->
    <div class="mt-14 space-y-10">

      <!-- SUGESTÕES -->
      <section class="card bg-base-100 shadow-sm border border-base-200 rounded-xl overflow-hidden">
        <div class="card-body p-6">
          <h3 class="card-title text-lg">Meus pedidos solicitados</h3>

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

                  <td class="text-sm">
                    {{ (s.authors || []).join(', ') || '-' }}
                  </td>

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

                  <td class="text-sm">
                    {{ formatDate(s.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- REQUISIÇÕES -->
      <section class="space-y-4">
        <a href="/requisitions" class="block space-y-4">
          <h3 class="text-xl font-bold text-base-content tracking-tight">
            Minhas requisições
          </h3>
        </a>

        <requisitions-table :user-is-admin="false" />
      </section>

    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { unwrapPage } from '../../api';
import { useFavoritesStore } from '../../stores/favoritesStore.js';

const favoritesStore = useFavoritesStore();

const favoriteBooks = computed(() =>
  Array.isArray(favoritesStore.favorites)
    ? favoritesStore.favorites
    : []
);

//Sugestões
const suggestions = ref([]);
const suggestionsLoading = ref(false);

async function loadSuggestions() {
  suggestionsLoading.value = true;

  try {
    const res = await window.axios.get('/api/book-suggestions');
    const page = unwrapPage(res);

    suggestions.value = Array.isArray(page?.data)
      ? page.data
      : (Array.isArray(page) ? page : []);

  } catch (e) {
    console.error('Load suggestions error:', e);
    suggestions.value = [];
  } finally {
    suggestionsLoading.value = false;
  }
}

//Utils
function statusLabel(s) {
  const labels = {
    pending: 'Pendente',
    approved: 'Aprovada',
    rejected: 'Rejeitada',
  };
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

function onSuggestionsRefresh() {
  loadSuggestions();
}

//INIT
onMounted(() => {
  favoritesStore.loadFavorites();
  loadSuggestions();
  window.addEventListener('suggestions-refresh', onSuggestionsRefresh);
});

onUnmounted(() => {
  window.removeEventListener('suggestions-refresh', onSuggestionsRefresh);
});
</script>