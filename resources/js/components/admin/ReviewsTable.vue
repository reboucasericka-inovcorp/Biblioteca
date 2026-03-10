<template>
  <div class="space-y-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <div class="flex flex-wrap items-center gap-4">
          <select v-model="status" class="select select-bordered h-10 min-w-[140px] bg-base-100">
            <option value="">Todos</option>
            <option value="suspended">Suspensos</option>
            <option value="active">Ativos</option>
            <option value="refused">Recusados</option>
          </select>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 shadow">
      <div class="card-body p-6">
        <div class="overflow-x-auto">
          <table class="table table-zebra w-full">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cidadão</th>
                <th>Livro</th>
                <th>Rating</th>
                <th>Comentário</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="review in reviews" :key="review.id">
                <td class="font-mono text-sm">{{ review.id }}</td>
                <td class="text-sm">{{ review.user?.name ?? '-' }}</td>
                <td class="text-sm">{{ review.book?.name ?? '-' }}</td>
                <td class="text-sm">{{ review.rating }}/5</td>
                <td class="text-sm max-w-[420px] truncate" :title="review.comment">{{ review.comment }}</td>
                <td>
                  <span class="badge badge-sm" :class="badgeClass(review.status)">
                    {{ review.status }}
                  </span>
                </td>
                <td>
                  <div class="flex items-center gap-2">
                    <a :href="`/reviews/${review.id}`" class="link link-primary text-sm">
                      Detalhe
                    </a>
                    <button
                      v-if="review.status === 'suspended'"
                      class="btn btn-sm btn-success"
                      :disabled="actionLoadingId === review.id"
                      @click="approve(review.id)"
                    >
                      Aprovar
                    </button>
                    <button
                      v-if="review.status === 'suspended'"
                      class="btn btn-sm btn-error"
                      :disabled="actionLoadingId === review.id"
                      @click="reject(review)"
                    >
                      Recusar
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="reviews.length === 0">
                <td colspan="7" class="text-center text-sm text-base-content/60 py-6">
                  Nenhum review encontrado.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-4">
          <button
            class="btn btn-sm btn-ghost"
            :disabled="pagination.current_page <= 1"
            @click="goToPage(pagination.current_page - 1)"
          >
            ←
          </button>
          <span class="flex items-center px-3 text-sm">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
          </span>
          <button
            class="btn btn-sm btn-ghost"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"
          >
            →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { unwrapPage } from '../../api';

const reviews = ref([]);
const status = ref('suspended');
const actionLoadingId = ref(null);
const pagination = ref({
  current_page: 1,
  last_page: 1,
});

async function load() {
  const res = await window.axios.get('/api/reviews', {
    params: {
      status: status.value || undefined,
      page: pagination.value.current_page,
    },
  });

  const pageData = unwrapPage(res);
  reviews.value = pageData.data ?? [];
  pagination.value.current_page = pageData.current_page ?? 1;
  pagination.value.last_page = pageData.last_page ?? 1;
}

function goToPage(page) {
  pagination.value.current_page = page;
  load();
}

function badgeClass(value) {
  return {
    'badge-warning': value === 'suspended',
    'badge-success': value === 'active',
    'badge-error': value === 'refused',
  };
}

async function approve(reviewId) {
  actionLoadingId.value = reviewId;
  try {
    await window.axios.patch(`/api/reviews/${reviewId}/approve`);
    window.showToast?.('Review aprovado com sucesso.', 'success');
    await load();
  } catch (error) {
    const message = error.response?.data?.message ?? 'Erro ao aprovar review.';
    window.showToast?.(message, 'error');
  } finally {
    actionLoadingId.value = null;
  }
}

async function reject(review) {
  const reason = window.prompt('Justificativa da recusa:');
  if (!reason || !reason.trim()) {
    window.showToast?.('A justificativa é obrigatória.', 'error');
    return;
  }

  actionLoadingId.value = review.id;
  try {
    await window.axios.patch(`/api/reviews/${review.id}/reject`, { reason });
    window.showToast?.('Review recusado com sucesso.', 'success');
    await load();
  } catch (error) {
    const message = error.response?.data?.message ?? 'Erro ao recusar review.';
    window.showToast?.(message, 'error');
  } finally {
    actionLoadingId.value = null;
  }
}

watch(status, () => {
  pagination.value.current_page = 1;
  load();
});

onMounted(() => {
  load();
});
</script>
