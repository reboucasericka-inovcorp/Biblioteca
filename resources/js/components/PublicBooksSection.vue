<template>
  <div>
    <!-- Pesquisar na Google Books -->
    <section class="py-16 bg-white">
      <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-2xl font-semibold text-night-blue mb-8">
          Pesquisar no Google
        </h2>
        <div class="flex gap-2 mb-6">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Pesquisar por título, autor..."
            class="input input-bordered flex-1"
            @keyup.enter="searchGoogle"
          />
          <button
            type="button"
            class="btn btn-primary"
            :disabled="searching || !searchQuery.trim()"
            @click="searchGoogle"
          >
            <span v-if="searching">A pesquisar...</span>
            <span v-else>Pesquisar</span>
          </button>
        </div>

        <!-- Grid de resultados Google -->
        <div
          v-if="googleResults.length > 0"
          class="flex flex-row flex-nowrap gap-4 overflow-x-auto overflow-y-hidden pb-4 scroll-smooth snap-x snap-mandatory"
        >
          <div
            v-for="v in googleResults"
            :key="v.google_volume_id"
            class="w-[140px] min-w-[140px] max-w-[140px] shrink-0 snap-start flex flex-col cursor-pointer"
            @click="openModal(v)"
          >
            <div class="bg-base-200 rounded-lg overflow-hidden shadow w-full">
              <div class="aspect-[2/3] w-full block">
                <img
                  v-if="v.thumbnail_url"
                  :src="v.thumbnail_url"
                  :alt="v.title"
                  class="w-full h-full object-cover block"
                  loading="lazy"
                />
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center text-xs text-base-content/50"
                >
                  Sem capa
                </div>
              </div>
            </div>
            <h3 class="mt-2 text-sm font-semibold leading-tight line-clamp-2 min-w-0 text-base-content">
              {{ v.title || '—' }}
            </h3>
            <p class="text-xs text-base-content/60 line-clamp-1 min-w-0">
              {{ Array.isArray(v.authors) ? v.authors.join(', ') : (v.authors || '—') }}
            </p>
          </div>
        </div>

        <p v-else-if="searched && !searching" class="text-base-content/60 text-sm">
          Nenhum resultado encontrado. Tente outra pesquisa.
        </p>
      </div>
    </section>

    <!-- Modal Google Book -->
    <dialog ref="modalRef" class="modal">
      <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg text-night-blue">
          {{ selectedVolume?.title || '—' }}
        </h3>
        <p v-if="selectedVolume?.authors?.length" class="text-sm text-base-content/70 mt-1">
          {{ selectedVolume.authors.join(', ') }}
        </p>
        <p v-if="selectedVolume?.publisher" class="text-sm text-base-content/60 mt-1">
          {{ selectedVolume.publisher }}
        </p>
        <p v-if="selectedVolume?.description" class="text-sm text-base-content/80 mt-3 line-clamp-6">
          {{ selectedVolume.description }}
        </p>
        <div class="modal-action">
          <button
            type="button"
            class="btn btn-primary"
            @click="handlePreviewClick"
          >
            {{ isLogged ? 'Ver preview' : 'Entrar para ver preview' }}
          </button>
          <form method="dialog">
            <button type="submit" class="btn">Fechar</button>
          </form>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button type="submit">fechar</button>
      </form>
    </dialog>

    <!-- Publicações Recentes -->
    <section class="py-16 bg-steel-gray/30">
      <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-2xl font-semibold text-night-blue mb-8">
          Publicações Recentes
        </h2>
        <books-grid :books="recentBooks" :is-logged="isLogged" @requisition="requisition" />
      </div>
    </section>

    <!-- Tecnologia & Desenvolvimento -->
    <section class="py-16 bg-white">
      <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-2xl font-semibold text-night-blue mb-8">
          Tecnologia & Desenvolvimento
        </h2>
        <books-grid :books="techBooks" :is-logged="isLogged" @requisition="requisition" />
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { unwrap } from '../api';
import BooksGrid from './BooksGrid.vue';

const recentBooks = ref([]);
const techBooks = ref([]);
const searchQuery = ref('');
const googleResults = ref([]);
const searching = ref(false);
const searched = ref(false);
const selectedVolume = ref(null);
const modalRef = ref(null);

const isLogged = computed(() => {
  const el = document.getElementById('app');
  return el?.dataset?.auth === '1';
});

async function searchGoogle() {
  const q = searchQuery.value?.trim();
  if (!q) return;

  searching.value = true;
  searched.value = true;

  try {
    const res = await window.axios.get('/api/google-books/search', {
      params: { q, maxResults: 20 },
    });
    googleResults.value = unwrap(res) ?? [];
  } catch (e) {
    googleResults.value = [];
  } finally {
    searching.value = false;
  }
}

function openModal(volume) {
  selectedVolume.value = volume;
  modalRef.value?.showModal();
}

function handlePreviewClick() {
  if (isLogged.value && selectedVolume.value?.google_volume_id) {
    const previewUrl = `https://books.google.com/books?id=${selectedVolume.value.google_volume_id}`;
    window.open(previewUrl, '_blank');
  } else {
    window.location.href = '/login';
  }
}

async function loadRecent() {
  const res = await window.axios.get('/api/books', {
    params: { type: 'recent', sort: 'created_at', dir: 'desc', per_page: 8 },
  });
  recentBooks.value = unwrap(res) ?? [];
}

async function loadTech() {
  const res = await window.axios.get('/api/books', {
    params: { type: 'tech', sort: 'created_at', dir: 'desc', per_page: 8 },
  });
  techBooks.value = unwrap(res) ?? [];
}

async function requisition(bookId) {
  try {
    await window.axios.post('/api/requisitions', { book_id: bookId });
    await loadRecent();
    await loadTech();
  } catch (e) {
    const msg = e.response?.data?.message || 'Não foi possível requisitar.';
    alert(msg);
  }
}

onMounted(() => {
  loadRecent();
  loadTech();
});
</script>
