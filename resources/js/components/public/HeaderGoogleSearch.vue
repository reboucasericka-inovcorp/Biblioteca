<template>
  <div class="relative w-full max-w-[520px] mx-auto">
    <div class="flex items-center bg-white rounded-full overflow-hidden border border-base-300 shadow-sm">
      <input
        v-model="query"
        type="text"
        placeholder="Buscar produtos..."
        class="w-full px-4 py-2 text-sm text-base-content outline-none border-0 focus:ring-0"
        @keyup.enter="search"
      />
      <button
        type="button"
        class="px-4 py-2 text-xs font-semibold uppercase bg-base-200 text-base-content hover:bg-base-300 transition"
        :disabled="searching || !query.trim()"
        @click="search"
      >
        {{ searching ? '...' : 'Buscar' }}
      </button>
    </div>

    <div
      v-if="results.length > 0"
      class="absolute z-50 top-full mt-2 w-full bg-white rounded-md border border-base-300 shadow-lg max-h-72 overflow-y-auto"
    >
      <button
        v-for="book in results"
        :key="book.google_volume_id"
        type="button"
        class="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-base-200/60 transition"
        @click="openGooglePreview(book)"
      >
        <img
          v-if="book.thumbnail_url"
          :src="book.thumbnail_url"
          :alt="book.title"
          class="w-8 h-11 object-cover rounded"
          loading="lazy"
        />
        <div v-else class="w-8 h-11 rounded bg-base-200" />
        <div class="min-w-0">
          <p class="text-xs font-semibold text-base-content line-clamp-1">{{ book.title || 'Sem título' }}</p>
          <p class="text-[11px] text-base-content/70 line-clamp-1">
            {{ Array.isArray(book.authors) ? book.authors.join(', ') : (book.authors || 'Autor desconhecido') }}
          </p>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { unwrap } from '../../api';

const query = ref('');
const results = ref([]);
const searching = ref(false);

async function search() {
  const q = query.value?.trim();
  if (!q) {
    results.value = [];
    return;
  }

  searching.value = true;
  try {
    const res = await window.axios.get('/api/google-books/search', { params: { q, maxResults: 8 } });
    results.value = unwrap(res) ?? [];
  } catch (e) {
    results.value = [];
  } finally {
    searching.value = false;
  }
}

function openGooglePreview(book) {
  const volumeId = book?.google_volume_id;
  if (!volumeId) return;
  window.open(`https://books.google.com/books?id=${volumeId}`, '_blank', 'noopener,noreferrer');
}
</script>
