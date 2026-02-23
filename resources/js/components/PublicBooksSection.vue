<template>
  <div>
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

const isLogged = computed(() => {
  const el = document.getElementById('app');
  return el?.dataset?.auth === '1';
});

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
