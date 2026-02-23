<template>
  <div>
    <div class="text-center mb-8">
      <h2 class="text-2xl font-semibold text-night-blue mb-6">
        Pesquisar no Catálogo
      </h2>
      <input
        v-model="search"
        type="text"
        placeholder="Pesquisar por título, ISBN ou autor..."
        class="input input-bordered w-full max-w-xl mx-auto"
      />
    </div>

    <h2 class="text-2xl font-semibold text-night-blue mb-6">
      Últimos Livros Adicionados
    </h2>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="table-auto w-full">
        <thead>
          <tr class="bg-steel-gray/30 border-b border-steel-gray">
            <th class="p-4 text-left text-night-blue">Capa</th>
            <th class="p-4 text-left text-night-blue">Nome</th>
            <th class="p-4 text-left text-night-blue">Autores</th>
            <th class="p-4 text-left text-night-blue">Disponibilidade</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in books" :key="b.id" class="border-b border-steel-gray/50 hover:bg-steel-gray/20">
            <td class="p-4">
              <img
                v-if="b.cover_url"
                :src="b.cover_url"
                :alt="b.name"
                class="h-16 w-12 object-cover rounded"
              />
              <span v-else class="text-night-blue/60 text-sm">—</span>
            </td>
            <td class="p-4 font-medium text-night-blue">
              <a v-if="isLogged" :href="`/books/${b.id}`" class="text-electric-blue hover:underline">{{ b.name }}</a>
              <span v-else class="text-night-blue/70">{{ b.name }}</span>
            </td>
            <td class="p-4 text-night-blue/70 text-sm">
              <span v-if="b.authors?.length">
                {{ b.authors.map(a => a.name).join(', ') }}
              </span>
              <span v-else class="text-night-blue/60">—</span>
            </td>
            <td class="p-4">
              <button
                v-if="isLogged && b.is_available"
                @click="requisition(b.id)"
                class="btn btn-xs btn-success inline-flex items-center gap-1"
                title="Requisitar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                Requisitar
              </button>
              <span v-else-if="!b.is_available" class="badge badge-error inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Indisponível
              </span>
              <span v-else class="badge badge-outline badge-ghost">
                Login necessário
              </span>
            </td>
          </tr>
          <tr v-if="books.length === 0">
            <td colspan="4" class="p-8 text-center text-night-blue/70">
              Nenhum livro encontrado.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { unwrapPage } from '../api';

const books = ref([]);
const search = ref('');

const isLogged = computed(() => {
  const el = document.getElementById('app');
  return el?.dataset?.auth === '1';
});

async function load() {
  const res = await window.axios.get('/api/books', {
    params: { search: search.value, sort: 'created_at', dir: 'desc' },
  });
  const pageData = unwrapPage(res);
  books.value = pageData.data ?? [];
}

async function requisition(bookId) {
  try {
    await window.axios.post('/api/requisitions', { book_id: bookId });
    await load();
  } catch (e) {
    const msg = e.response?.data?.message || 'Não foi possível requisitar.';
    alert(msg);
  }
}

watch(search, () => load());

onMounted(() => load());
</script>
