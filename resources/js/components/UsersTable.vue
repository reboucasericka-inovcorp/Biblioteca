<template>
  <div class="space-y-4">
    <!-- Barra de filtros -->
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <div class="flex flex-wrap items-center gap-4">
          <input
            v-model="search"
            type="text"
            placeholder="Pesquisar por nome ou email"
            class="input input-bordered h-10 w-72"
          >
          <select v-model="sort" class="select select-bordered h-10 min-w-[120px] bg-base-100">
            <option value="name">Nome</option>
            <option value="email">Email</option>
          </select>
          <select v-model="dir" class="select select-bordered h-10 min-w-[100px] bg-base-100">
            <option value="asc">ASC</option>
            <option value="desc">DESC</option>
          </select>
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
                <th class="whitespace-nowrap min-w-[8rem]">Nome</th>
                <th class="whitespace-nowrap min-w-[12rem]">Email</th>
                <th class="whitespace-nowrap min-w-[10rem]">Role</th>
                <th class="whitespace-nowrap min-w-[8rem]">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in users" :key="u.id">
                <td class="p-4 text-sm font-medium">{{ u.name }}</td>
                <td class="p-4 text-sm">{{ u.email }}</td>
                <td class="p-4">
                  <span
                    :class="u.role === 'Admin' ? 'badge badge-primary' : 'badge badge-ghost'"
                  >
                    {{ u.role }}
                  </span>
                </td>
                <td class="p-4">
                  <div class="flex items-center gap-2">
                    <select
                      :value="u.role"
                      class="select select-bordered select-sm max-w-[8rem]"
                      @change="updateRole(u, $event)"
                    >
                      <option value="Admin">Admin</option>
                      <option value="Cidadao">Cidadão</option>
                    </select>
                    <a
                      :href="`/users/${u.id}`"
                      class="link link-primary text-sm"
                      title="Ver detalhe"
                    >
                      Detalhe
                    </a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginação -->
        <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-4">
          <button
            class="btn btn-sm btn-ghost"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            ←
          </button>
          <span class="flex items-center px-3 text-sm">
            Página {{ currentPage }} de {{ lastPage }}
          </span>
          <button
            class="btn btn-sm btn-ghost"
            :disabled="currentPage >= lastPage"
            @click="goToPage(currentPage + 1)"
          >
            →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { unwrapPage } from '../api';

const users = ref([]);
const search = ref('');
const sort = ref('name');
const dir = ref('asc');
const currentPage = ref(1);
const lastPage = ref(1);

async function load() {
  const res = await window.axios.get('/api/users', {
    params: {
      search: search.value,
      sort: sort.value,
      dir: dir.value,
      page: currentPage.value,
    },
  });
  const pageData = unwrapPage(res);
  users.value = pageData.data ?? [];
  currentPage.value = pageData.current_page ?? 1;
  lastPage.value = pageData.last_page ?? 1;
}

function goToPage(page) {
  currentPage.value = page;
  load();
}

async function updateRole(user, event) {
  const newRole = event.target.value;
  try {
    await window.axios.patch(`/api/users/${user.id}/role`, { role: newRole });
    user.role = newRole;
    if (window.showToast) {
      window.showToast('Role atualizada com sucesso.', 'success');
    }
  } catch (err) {
    const msg = err.response?.data?.message ?? 'Erro ao atualizar role.';
    if (window.showToast) {
      window.showToast(msg, 'error');
    } else {
      alert(msg);
    }
    event.target.value = user.role;
  }
}

watch([search, sort, dir], () => {
  currentPage.value = 1;
  load();
});

onMounted(() => load());
</script>
