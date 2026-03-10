<template>
  <div class="card bg-base-100 shadow mt-6">
    <div class="card-body p-6">
      <h3 class="card-title text-lg">
        {{ userIsAdmin ? 'Importar da Google Books' : 'Pesquisar na Google Books' }}
      </h3>
      <p class="text-sm text-base-content/70">
        {{ userIsAdmin
          ? 'Pesquise livros no Google Books e importe para a base de dados local.'
          : 'Pesquise livros e requisite os que existem no catálogo ou sugira aquisição.'
        }}
      </p>

      <div class="flex gap-2 mt-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Pesquisar por título, autor ou ISBN..."
          class="input input-bordered flex-1"
          @keyup.enter="search"
        />
        <button
          type="button"
          class="btn btn-primary"
          :disabled="searching || !searchQuery.trim()"
          @click="search"
        >
          <span v-if="searching">A pesquisar...</span>
          <span v-else>Pesquisar</span>
        </button>
      </div>

      <div v-if="searchError" class="alert alert-warning mt-4">
        <span>{{ searchError }}</span>
      </div>

      <div v-if="results.length > 0" class="mt-6">
        <h4 class="font-semibold mb-3">Resultados ({{ results.length }})</h4>
        <div class="space-y-4 max-h-96 overflow-y-auto">
          <div
            v-for="v in results"
            :key="v.google_volume_id"
            class="flex gap-4 p-4 rounded-lg border border-base-300 bg-base-200/50"
          >
            <div class="flex-shrink-0">
              <img
                v-if="v.thumbnail_url"
                :src="v.thumbnail_url"
                :alt="v.title"
                class="h-24 w-16 object-cover rounded"
              />
              <div
                v-else
                class="h-24 w-16 rounded bg-base-300 flex items-center justify-center text-base-content/50 text-xs"
              >
                Sem capa
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h5 class="font-medium line-clamp-2">{{ v.title }}</h5>
              <p v-if="v.authors?.length" class="text-sm text-base-content/70 mt-1">
                {{ v.authors.join(', ') }}
              </p>
              <p v-if="v.publisher" class="text-sm text-base-content/60">
                {{ v.publisher }}
                <span v-if="v.published_date"> · {{ v.published_date }}</span>
              </p>
              <p v-if="v.isbn_13" class="text-xs font-mono text-base-content/60 mt-1">
                ISBN-13: {{ v.isbn_13 }}
              </p>
              <span
                v-if="!userIsAdmin && v.in_catalog"
                class="badge badge-success badge-sm mt-1"
              >
                No catálogo
              </span>
            </div>
            <div class="flex-shrink-0 flex items-center">
              <template v-if="userIsAdmin">
                <button
                  type="button"
                  class="btn btn-sm btn-success"
                  :disabled="actionId === v.google_volume_id"
                  @click="importBook(v)"
                >
                  <span v-if="actionId === v.google_volume_id">A importar...</span>
                  <span v-else>Importar</span>
                </button>
              </template>
              <template v-else>
                <button
                  v-if="v.in_catalog && v.book_id"
                  type="button"
                  class="btn btn-sm btn-primary"
                  :disabled="actionId === v.google_volume_id"
                  @click="requisitar(v)"
                >
                  <span v-if="actionId === v.google_volume_id">A processar...</span>
                  <span v-else>Requisitar</span>
                </button>
                <button
                  v-else
                  type="button"
                  class="btn btn-sm btn-secondary"
                  :disabled="actionId === v.google_volume_id"
                  @click="sugerirAquisicao(v)"
                >
                  <span v-if="actionId === v.google_volume_id">A enviar...</span>
                  <span v-else>Sugerir aquisição</span>
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="searched && !searching" class="mt-6 text-base-content/60 text-sm">
        Nenhum resultado encontrado. A API externa pode estar indisponível.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { unwrap } from '../../api';

const props = defineProps({
  userIsAdmin: { type: Boolean, default: false },
});

const emit = defineEmits(['imported', 'suggested', 'requisitioned']);

const searchQuery = ref('');
const results = ref([]);
const searching = ref(false);
const searchError = ref('');
const searched = ref(false);
const actionId = ref(null);

async function search() {
  const q = searchQuery.value?.trim();
  if (!q) return;

  searching.value = true;
  searchError.value = '';
  searched.value = true;

  try {
    const params = { q, maxResults: 20 };
    if (!props.userIsAdmin) params.enrich_catalog = 1;
    const res = await window.axios.get('/api/google-books/search', { params });
    results.value = res.data?.data ?? [];
    if (results.value.length === 0 && !res.data?.data) {
      searchError.value = 'A pesquisa não retornou resultados. A API pode estar indisponível.';
    }
  } catch (e) {
    console.error('Google Books search error:', e);
    results.value = [];
    searchError.value = 'Erro ao pesquisar. A API externa pode estar indisponível.';
  } finally {
    searching.value = false;
  }
}

async function importBook(volume) {
  actionId.value = volume.google_volume_id;
  try {
    const res = await window.axios.post('/api/google-books/import', { volume });
    if (res.status === 201) {
      emit('imported', unwrap(res));
      window.dispatchEvent(new CustomEvent('books-refresh'));
    }
  } catch (e) {
    console.error('Import error:', e);
    const msg = e.response?.data?.message ?? 'Erro ao importar. Tente novamente.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
  }
}

async function requisitar(volume) {
  if (!volume.book_id) return;
  actionId.value = volume.google_volume_id;
  try {
    const res = await window.axios.post('/api/requisitions', { book_id: volume.book_id });
    if (res.status === 201) {
      emit('requisitioned');
      window.dispatchEvent(new CustomEvent('requisitions-refresh'));
      window.showToast('Requisição criada com sucesso.');
    }
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao requisitar. Tente novamente.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
  }
}

async function sugerirAquisicao(volume) {
  actionId.value = volume.google_volume_id;
  try {
    const res = await window.axios.post('/api/book-suggestions', {
      google_volume_id: volume.google_volume_id,
      title: volume.title,
      authors: volume.authors ?? [],
      thumbnail_url: volume.thumbnail_url ?? null,
    });
    if (res.status === 201) {
      emit('suggested');
      window.dispatchEvent(new CustomEvent('suggestions-refresh'));
      window.showToast('Sugestão enviada com sucesso.');
    }
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao enviar sugestão. Tente novamente.';
    window.showToast(msg, 'error');
  } finally {
    actionId.value = null;
  }
}
</script>
