<template>
  <div>
    <!-- BLOCO DE FILTROS (UI) -->    
    <div class="flex items-center gap-5 mb-4">
      <!-- Campo de pesquisa -->
      <input v-model="search" type="text" placeholder="Search by name or ISBN" class="border rounded px-9 h-10 w-72" >

      <!-- Select ordenação -->
      <select v-model="sort" class="border rounded px-9 h-10 appearance-none bg-white" >
        <option value="name">Name</option>
        <option value="isbn">ISBN</option>
        <option value="price">Price</option>
      </select>

      <!-- Select: direção da ordenação -->
      <select v-model="dir" class="border rounded px-9 h-10 appearance-none bg-white" >
        <option value="asc">ASC</option>
        <option value="desc">DESC</option>
      </select>
    </div>

    <!--  TABELA DE DADOS -->
    <!-- Estrutura base da tabela -->
    <table class="table-auto w-full border">

      <!-- Cabeçalho da tabela -->
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2">ISBN</th>
          <th class="p-2">Name</th>
          <th class="p-2">Publisher</th>
          <th class="p-2">Authors</th>
          <th class="p-2">Price</th>
        </tr>
      </thead>

      <!-- Corpo da tabela -->
      <!-- NMA -->
      <tbody>
        <tr v-for="b in books" :key="b.id">
          <td class="p-2">{{ b.isbn }}</td>
          <td class="p-2">{{ b.name }}</td>
          <td class="p-2">{{ b.publisher?.name }}</td>

          <td class="p-2">
            <span
              v-for="a in b.authors"
              :key="a.id"
              class="mr-1"
            >
              {{ a.name }}
            </span>
          </td>

          <td class="p-2">{{ b.price }} €</td>
        </tr>
      </tbody>

    </table>
  </div>
</template>

<script setup>
/* ===============================
   IMPORTS BASE DO VUE
   =============================== */
import { ref, onMounted, watch } from 'vue';

/* ===============================
   
   =============================== */
const books = ref([]);     // lista de livros vinda da API
const search = ref('');    // texto de pesquisa
const sort = ref('name');  // campo de ordenação
const dir = ref('asc');    // direção (asc | desc)

/* ===============================
   api
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
/* ===============================
   REAGIR A FILTROS (search / sort)
   =============================== */
watch([search, sort, dir], () => {
  load();
});



/* ===============================
   
   =============================== */
onMounted(load);
</script>
