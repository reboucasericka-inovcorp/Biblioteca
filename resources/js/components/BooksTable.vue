<template>
  <div>
    <!-- Barra de filtros + Criar -->
    <div class="flex flex-wrap items-center gap-6 p-5 bg-gray-50 rounded-lg border border-gray-200 mb-6">
      <input
        v-model="search"
        type="text"
        placeholder="Search by name or ISBN"
        class="border border-gray-300 rounded-md px 9 h-10 w-72"
      >
      <select v-model="sort" class="border border-gray-300 rounded-md px-9 h-10 min-w-[120px] bg-white">
        <option value="name">Name</option>
        <option value="isbn">ISBN</option>
        <option value="price">Price</option>
      </select>
      <select v-model="dir" class="border border-gray-300 rounded-md px-9 h-10 min-w-[100px] bg-white">
        <option value="asc">ASC</option>
        <option value="desc">DESC</option>
      </select>
      <a
        v-if="userIsAdmin"
        href="/books/create"
        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Criar Livro
      </a>
      <button
        type="button"
        @click="exportExcel"
        :disabled="exporting"
        class="ml-auto inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg v-if="!exporting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span v-if="exporting">Exporting...</span>
        <span v-else>Export Excel</span>
      </button>
    </div>

    <!-- Tabela de dados -->
    <div class="overflow-x-auto">
      <table class="table-auto w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3 text-left">Cover</th>
            <th class="p-3 text-left">ISBN</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Publisher</th>
            <th class="p-3 text-left">Authors</th>
            <th class="p-3 text-left">Bibliography</th>
            <th class="p-3 text-left">Price</th>
            <th class="p-3 text-left">Ações</th>
            <th v-if="userIsAdmin" class="p-3 text-left">Admin</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in books" :key="b.id" class="hover:bg-gray-50">
            <td class="p-3">
              <img 
                v-if="b.cover_url" 
                :src="b.cover_url" 
                :alt="b.name + ' cover'"
                class="h-16 w-12 object-cover rounded"
              />
              <span v-else class="text-gray-400 text-xs">No cover</span>
            </td>
            <td class="p-3 font-mono text-sm">{{ b.isbn }}</td>
            <td class="p-3 font-medium">
              <a :href="`/books/${b.id}`" class="text-blue-600 hover:underline">{{ b.name }}</a>
            </td>
            <td class="p-3">{{ b.publisher?.name || '-' }}</td>
            <td class="p-3">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="a in b.authors"
                  :key="a.id"
                  class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"
                >
                  {{ a.name }}
                </span>
                <span v-if="!b.authors || b.authors.length === 0" class="text-gray-400">-</span>
              </div>
            </td>
            <td class="p-3 text-sm text-gray-700 max-w-xs">
              <div class="line-clamp-2">{{ b.bibliography || '-' }}</div>
            </td>
            <td class="p-3 font-semibold">{{ parseFloat(b.price).toFixed(2) }} €</td>
            <td class="p-3">
              <button
                v-if="b.is_available"
                @click="requisition(b.id)"
                class="btn btn-xs btn-success inline-flex items-center gap-1"
                title="Requisitar este livro"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                Requisitar
              </button>
              <span v-else class="badge badge-error inline-flex items-center gap-1" title="Livro indisponível">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Indisponível
              </span>
            </td>
            <td v-if="userIsAdmin" class="p-3">
              <div class="flex items-center gap-3">
                <a
                  :href="`/books/${b.id}/edit`"
                  class="text-blue-600 hover:text-blue-800 transition-colors"
                  title="Editar"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </a>
                <form :action="`/books/${b.id}`" method="POST" class="inline" @submit="confirmDelete">
                  <input type="hidden" name="_token" :value="csrfToken">
                  <input type="hidden" name="_method" value="DELETE">
                  <button
                    type="submit"
                    class="text-red-600 hover:text-red-800 transition-colors cursor-pointer bg-transparent border-none p-0"
                    title="Eliminar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-4v6m4-6v6M1 7h22" />
                    </svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Link de exportação abaixo da tabela -->
    <div class="mt-4 flex items-center gap-4">
      <button
        type="button"
        @click="exportExcel"
        :disabled="exporting"
        class="inline-flex items-center gap-2 text-green-600 hover:text-green-800 font-medium disabled:opacity-50"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span v-if="exporting">Exporting...</span>
        <span v-else>Download books as Excel</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

defineProps({
  userIsAdmin: { type: Boolean, default: false },
});

const books = ref([]);
const csrfToken = ref('');
const search = ref('');    // texto de pesquisa
const sort = ref('name');  // campo de ordenação
const dir = ref('asc');    // direção (asc | desc)
const exporting = ref(false);

/* ===============================
   API
   =============================== */
async function load() {
  const params = new URLSearchParams({
    search: search.value,
    sort: sort.value,
    dir: dir.value,
  });

  const res = await fetch(`/api/books?${params.toString()}`);
  const json = await res.json();

  books.value = json.data;
}

async function requisition(bookId) {
  try {
    await window.axios.post('/api/requisitions', { book_id: bookId });
    await load();
  } catch (e) {
    console.error('Requisition error:', e);
    const msg = e.response?.data?.message || 'Não foi possível requisitar o livro. Tente novamente.';
    alert(msg);
  }
}

/* ===============================
   EXPORTAR EXCEL
   =============================== */
async function exportExcel() {
  exporting.value = true;
  try {
    const params = new URLSearchParams({
      search: search.value,
      sort: sort.value,
      dir: dir.value,
    });
    // Rota web (com auth) para o download usar a mesma sessão
    const res = await fetch(`${window.location.origin}/books/export?${params.toString()}`, {
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    if (!res.ok) {
      const text = await res.text();
      throw new Error(text || `Export failed (${res.status})`);
    }
    const blob = await res.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `books_${new Date().toISOString().split('T')[0]}.xlsx`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  } catch (e) {
    console.error('Export error:', e);
    alert('Export failed. Please try again or check the console.');
  } finally {
    exporting.value = false;
  }
}

/* ===============================
   REAGIR A FILTROS (search / sort)
   =============================== */
watch([search, sort, dir], () => {
  load();
});

function confirmDelete(e) {
  if (!confirm('Tem certeza que deseja eliminar este livro?')) {
    e.preventDefault();
  }
}

onMounted(() => {
  csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  load();
});
</script>
