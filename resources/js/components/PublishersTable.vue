<template>
  <div>
    <!-- Barra de filtros + Criar -->
    <div class="flex flex-wrap items-center gap-6 p-5 bg-gray-50 rounded-lg border border-gray-200 mb-6">
      <input
        v-model="search"
        type="text"
        placeholder="Search by publisher name"
        class="border border-gray-300 rounded-md px-9 h-10 w-72"
      >
      <select v-model="sort" class="border border-gray-300 rounded-md px-9 h-10 min-w-[120px] bg-white">
        <option value="name">Name</option>
      </select>
      <select v-model="dir" class="border border-gray-300 rounded-md px-9 h-10 min-w-[100px] bg-white">
        <option value="asc">ASC</option>
        <option value="desc">DESC</option>
      </select>
      <a
        v-if="userIsAdmin"
        href="/publishers/create"
        class="ml-auto inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Criar Editora
      </a>
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto">
      <table class="table-auto w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3 text-left">Logo</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Notes</th>
            <th v-if="userIsAdmin" class="p-3 text-left">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in publishers" :key="p.id" class="hover:bg-gray-50">
            <td class="p-3">
              <img 
                v-if="p.logo_url" 
                :src="p.logo_url" 
                :alt="p.name + ' logo'"
                class="h-12 w-12 object-contain"
              />
              <span v-else class="text-gray-400 text-sm">No logo</span>
            </td>
            <td class="p-3 font-medium">{{ p.name }}</td>
            <td class="p-3 text-sm text-gray-700">
              <div class="max-w-md">{{ p.notes || '-' }}</div>
            </td>
            <td v-if="userIsAdmin" class="p-3 flex gap-2">
              <a :href="`/publishers/${p.id}/edit`" class="btn btn-sm btn-outline">Editar</a>
              <form :action="`/publishers/${p.id}`" method="POST" class="inline" @submit="confirmDelete">
                <input type="hidden" name="_token" :value="csrfToken">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-sm btn-error">Eliminar</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

defineProps({
  userIsAdmin: { type: Boolean, default: false },
});

const publishers = ref([]);
const csrfToken = ref('');
const search = ref('');
const sort = ref('name');
const dir = ref('asc');

/* ===============================
   API
   =============================== */
async function load() {
  const params = new URLSearchParams({
    search: search.value,
    sort: sort.value,
    dir: dir.value,
  });

  const res = await fetch(`/api/publishers?${params.toString()}`);
  const json = await res.json();

  publishers.value = json.data;
}

/* ===============================
   REAGIR A FILTROS
   =============================== */
watch([search, sort, dir], load);

function confirmDelete(e) {
  if (!confirm('Tem certeza que deseja eliminar esta editora?')) {
    e.preventDefault();
  }
}

onMounted(() => {
  csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  load();
});
</script>
