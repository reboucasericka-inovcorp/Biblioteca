<template>
  <div class="card bg-base-100 shadow border border-gray-100">
    <div class="card-body p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Stock — reconciliação e auditoria</h3>
      <p class="text-sm text-gray-600 mb-4">
        Ajuste legado (stock vs empréstimos active/late), rollback da última execução e lista de inconsistências.
      </p>

      <div class="flex flex-wrap gap-2 mb-6">
        <button
          type="button"
          class="btn btn-sm btn-primary"
          :disabled="busy"
          @click="reconcile"
        >
          Reconciliar stock
        </button>
        <button
          type="button"
          class="btn btn-sm btn-outline"
          :disabled="busy"
          @click="rollback"
        >
          Reverter última reconciliação
        </button>
        <button
          type="button"
          class="btn btn-sm btn-ghost"
          :disabled="busy"
          @click="loadInconsistencies"
        >
          Atualizar lista
        </button>
      </div>

      <div v-if="inconsistencies.length > 0" class="overflow-x-auto">
        <p class="text-sm font-medium text-amber-800 mb-2">
          Inconsistências (stock &lt; empréstimos active+late): {{ inconsistencies.length }}
        </p>
        <table class="table table-zebra table-sm w-full text-sm">
          <thead>
            <tr>
              <th>Livro</th>
              <th class="text-right">Stock</th>
              <th class="text-right">Em empréstimo</th>
              <th class="text-right">Diferença</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in inconsistencies" :key="row.id">
              <td>
                <a :href="`/books/${row.id}`" class="link link-primary">{{ row.title }}</a>
              </td>
              <td class="text-right">{{ row.stock }}</td>
              <td class="text-right">{{ row.out_on_loan }}</td>
              <td class="text-right">{{ row.diff }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-sm text-gray-500">Sem inconsistências detetadas.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const busy = ref(false);
const inconsistencies = ref([]);

async function loadInconsistencies() {
  busy.value = true;
  try {
    const res = await window.axios.get('/admin/stock/inconsistencies');
    inconsistencies.value = res.data?.data ?? [];
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Erro ao carregar inconsistências.';
    window.showToast?.(msg, 'error');
  } finally {
    busy.value = false;
  }
}

function toastFromResponse(res, okMsg) {
  const ok = res.data?.ok !== false;
  const text = res.data?.message ?? (ok ? okMsg : 'Operação falhou.');
  window.showToast?.(text, ok ? 'success' : 'error');
  if (res.data?.output) {
    console.info(res.data.output);
  }
}

async function reconcile() {
  if (!window.confirm('Executar reconciliação de stock? (apenas livros elegíveis serão alterados)')) {
    return;
  }
  busy.value = true;
  try {
    const res = await window.axios.post('/admin/stock/reconcile');
    toastFromResponse(res, 'Reconciliação concluída.');
    await loadInconsistencies();
  } catch (e) {
    if (e.response) {
      toastFromResponse(e.response, '');
    } else {
      window.showToast?.(e.message ?? 'Erro de rede.', 'error');
    }
  } finally {
    busy.value = false;
  }
}

async function rollback() {
  if (!window.confirm('Reverter a última reconciliação de stock?')) {
    return;
  }
  busy.value = true;
  try {
    const res = await window.axios.post('/admin/stock/rollback');
    toastFromResponse(res, 'Rollback concluído.');
    await loadInconsistencies();
  } catch (e) {
    if (e.response) {
      toastFromResponse(e.response, '');
    } else {
      window.showToast?.(e.message ?? 'Erro de rede.', 'error');
    }
  } finally {
    busy.value = false;
  }
}

onMounted(loadInconsistencies);
</script>
