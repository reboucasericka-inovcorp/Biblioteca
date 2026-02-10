<template>
  <div>
    <!-- Filtros -->
    <div class="flex items-center gap-5 mb-4">
      <input v-model="search" type="text" placeholder="Search by author name" class="border rounded px-9 h-10 w-72" >

      <select v-model="sort" class="border rounded px-9 h-10 appearance-none bg-white" >
        <option value="name">Name</option>
      </select>

      <select v-model="dir" class="border rounded px-9 h-10 appearance-none bg-white" >
        <option value="asc">ASC</option>
        <option value="desc">DESC</option>
      </select>
    </div>

    <!-- Tabela -->
    <table class="table-auto w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2">Name</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="a in authors" :key="a.id">
          <td class="p-2">{{ a.name }}</td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

/* ===============================
   STATE
   =============================== */
const authors = ref([]);
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

  const res = await fetch(`/api/authors?${params.toString()}`);
  const json = await res.json();

  authors.value = json.data;
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
