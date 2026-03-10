<template>
  <div class="space-y-4">
    <!-- Barra de filtros + Criar -->
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <div class="flex flex-wrap items-center gap-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search by author name"
        class="input input-bordered h-10 w-72"
      >
      <select v-model="sort" class="select select-bordered h-10 min-w-[120px] bg-base-100">
        <option value="name">Name</option>
      </select>
      <select v-model="dir" class="select select-bordered h-10 min-w-[100px] bg-base-100">
        <option value="asc">ASC</option>
        <option value="desc">DESC</option>
      </select>
      <a
        v-if="userIsAdmin"
        href="/authors/create"
        class="btn btn-primary"
      >
        Criar Autor
      </a>
        </div>
      </div>
    </div>

    <!-- Tabela -->
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
    <div class="overflow-x-auto">
      <table class="table table-zebra w-full">
        <thead>
          <tr>
            <th>Photo</th>
            <th class="whitespace-nowrap min-w-[8rem]">Name</th>
            <th v-if="userIsAdmin" class="whitespace-nowrap min-w-[10rem]">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in authors" :key="a.id">
            <td class="p-4">
              <img 
                v-if="a.photo_url" 
                :src="a.photo_url" 
                :alt="a.name + ' photo'"
                class="h-12 w-12 object-cover rounded-full"
              />
              <span v-else class="text-base-content/60 text-sm">No photo</span>
            </td>
            <td class="p-4 text-sm font-medium whitespace-nowrap">{{ a.name }}</td>
            <td v-if="userIsAdmin" class="p-4">
              <div class="flex items-center gap-4">
                <a
                  :href="`/authors/${a.id}/edit`"
                  class="link link-primary"
                  title="Editar"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </a>
                <form :action="`/authors/${a.id}`" method="POST" class="inline" @submit="confirmDelete">
                  <input type="hidden" name="_token" :value="csrfToken">
                  <input type="hidden" name="_method" value="DELETE">
                  <button
                    type="submit"
                    class="link link-error cursor-pointer bg-transparent border-none p-0"
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { unwrapPage } from '../../api';

defineProps({
  userIsAdmin: { type: Boolean, default: false },
});

const authors = ref([]);
const csrfToken = ref('');
const search = ref('');
const sort = ref('name');
const dir = ref('asc');

/* ===============================
   API
   =============================== */
async function load() {
  const res = await window.axios.get('/api/authors', {
    params: { search: search.value, sort: sort.value, dir: dir.value },
  });
  const pageData = unwrapPage(res);
  authors.value = pageData.data ?? [];
}

/* ===============================
   REAGIR A FILTROS
   =============================== */
watch([search, sort, dir], () => load());

function confirmDelete(e) {
  if (!confirm('Tem certeza que deseja eliminar este autor?')) {
    e.preventDefault();
  }
}

onMounted(() => {
  csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  load();
});
</script>
