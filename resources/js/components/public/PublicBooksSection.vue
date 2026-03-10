<template>
  <div>
    <books-grid
      v-if="mode === 'featured'"
      :books="featuredBooks"
      :is-logged="isLogged"
      @requisition="requisition"
    />
    <books-grid
      v-if="mode === 'recent'"
      :books="recentBooks"
      :is-logged="isLogged"
      @requisition="requisition"
    />
    <books-grid
      v-if="mode === 'tech'"
      :books="techBooks"
      :is-logged="isLogged"
      @requisition="requisition"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { unwrap } from '../../api';
import BooksGrid from './BooksGrid.vue';

// Homepage: featured 6 + recent 30 + tech 6 = 42 livros. Se /api/books?type=recent&per_page=30 > 300–400ms, considerar cache ou índice.
const props = defineProps({
  mode: { type: String, required: true },
});

const featuredBooks = ref([]);
const recentBooks = ref([]);
const techBooks = ref([]);

const isLogged = computed(() => document.body.dataset.auth === '1');

async function loadFeatured() {
  const res = await window.axios.get('/api/books', {
    params: { type: 'featured', per_page: 6 },
  });
  featuredBooks.value = unwrap(res) ?? [];
}

async function loadRecent() {
  const res = await window.axios.get('/api/books', {
    params: { type: 'recent', per_page: 30 },
  });
  recentBooks.value = unwrap(res) ?? [];
}

async function loadTech() {
  const res = await window.axios.get('/api/books', {
    params: { type: 'tech', per_page: 6 },
  });
  techBooks.value = unwrap(res) ?? [];
}

function loadForMode() {
  if (props.mode === 'featured') loadFeatured();
  if (props.mode === 'recent') loadRecent();
  if (props.mode === 'tech') loadTech();
}

async function requisition(bookId) {
  try {
    await window.axios.post('/api/requisitions', { book_id: bookId });
    loadForMode();
  } catch (e) {
    const msg = e.response?.data?.message || 'Não foi possível requisitar.';
    window.showToast(msg, 'error');
  }
}

onMounted(loadForMode);
watch(() => props.mode, loadForMode);
</script>
