<template>
  <div>
    <!--  Filtros -->
    <div class="flex items-center gap-5 mb-4">
      <!-- Campo de pesquisa -->
      <input v-model="search" type="text" placeholder="Search by publisher name" class="border rounded px-9 h-10 w-72" >

      <select v-model="sort" class="border rounded px-9 h-10 appearance-none bg-white" >
        <option value="name">Name</option>
      </select>

      <select v-model="dir" class="border rounded px-9 h-10 appearance-none bg-white" >
        <option value="asc">ASC</option>
        <option value="desc">DESC</option>
      </select>
    </div>

    <!--  Tabela -->
    <table class="table-auto w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2">Name</th>
          <th class="p-2">Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in publishers" :key="p.id">
          <td class="p-2">{{ p.name }}</td>
          <td class="p-2 text-sm text-gray-700">
            {{ p.notes }}
          </td>
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
