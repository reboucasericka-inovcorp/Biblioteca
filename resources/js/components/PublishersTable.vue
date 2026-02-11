<template>
  <div>
    <!-- Barra de filtros -->
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
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto">
      <table class="table-auto w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3 text-left">Logo</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in publishers" :key="p.id" class="hover:bg-gray-50">
            <td class="p-3">
              <img 
                v-if="p.logo" 
                :src="p.logo" 
                :alt="p.name + ' logo'"
                class="h-12 w-12 object-contain"
              />
              <span v-else class="text-gray-400 text-sm">No logo</span>
            </td>
            <td class="p-3 font-medium">{{ p.name }}</td>
            <td class="p-3 text-sm text-gray-700">
              <div class="max-w-md">{{ p.notes || '-' }}</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

/* ===============================
   STATE
   =============================== */
const publishers = ref([]);
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

/* ===============================
   INIT
   =============================== */
onMounted(load);
</script>
