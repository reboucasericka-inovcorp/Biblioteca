<template>
  <div class="relative group">
    <button
      type="button"
      class="flex items-center gap-2 font-semibold uppercase tracking-wide"
      aria-haspopup="true"
      aria-expanded="false"
    >
      <span>☰</span>
      <span>Categorias</span>
    </button>
    <div
      class="absolute left-0 top-full mt-2 w-[760px] max-w-[95vw] bg-white text-gray-800 rounded-sm shadow-2xl border border-gray-200 z-50 hidden group-hover:block group-focus-within:block"
    >
      <div class="px-5 py-4 border-b border-gray-100 text-[11px] font-semibold uppercase tracking-wider text-gray-600">
        ☰ Categorias
      </div>
      <div v-if="loading" class="p-5 text-center text-gray-500 text-sm">
        A carregar...
      </div>
      <div
        v-else-if="columns.length > 0"
        class="grid grid-cols-1 md:grid-cols-3 gap-4 p-5 text-[12px] leading-6"
      >
        <ul
          v-for="(col, i) in columns"
          :key="i"
          class="space-y-1 uppercase"
        >
          <li v-for="cat in col" :key="cat.id">
            <a
              :href="categoryUrl(cat)"
              class="hover:text-primary"
            >
              {{ cat.name }}
            </a>
          </li>
        </ul>
      </div>
      <div v-else class="p-5 text-center text-gray-500 text-sm">
        Nenhuma categoria disponível.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
  booksBaseUrl: { type: String, default: '/books' },
});

const categories = ref([]);
const loading = ref(true);

const columns = computed(() => {
  const list = categories.value;
  if (!list.length) return [];
  const n = Math.max(1, Math.ceil(list.length / 3));
  return [list.slice(0, n), list.slice(n, n * 2), list.slice(n * 2)];
});

function categoryUrl(cat) {
  const base = props.booksBaseUrl || '/books';
  const sep = base.includes('?') ? '&' : '?';
  return `${base}${sep}category=${encodeURIComponent(cat.slug || cat.id)}`;
}

async function loadCategories() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/categories', { withCredentials: true });
    const data = res.data?.data;
    categories.value = Array.isArray(data) ? data : [];
  } catch {
    categories.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(loadCategories);
</script>
