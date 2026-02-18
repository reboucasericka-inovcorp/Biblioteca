<template>
  <div class="card bg-base-100 shadow">
    <div class="card-body p-6 space-y-4">
    <h3 class="text-2xl font-bold text-base-content">{{ book.name }}</h3>

    <img
      v-if="book.cover_url"
      :src="book.cover_url"
      class="w-40 rounded shadow"
      :alt="book.name"
    />

    <p class="text-sm text-base-content">{{ book.bibliography }}</p>

    <div>
      <button
        v-if="book.is_available"
        @click="requisition"
        class="btn btn-primary"
      >
        Requisitar
      </button>

      <span v-else class="badge badge-error">
        Indisponível
      </span>
    </div>

    <h4 class="text-lg font-semibold text-base-content">
      Histórico de Requisições
    </h4>

    <div
      v-for="req in book.requisitions"
      :key="req.id"
      class="text-sm text-base-content border-b border-base-300 py-4"
    >
      <strong>#{{ req.sequential_number }}</strong>
      — {{ req.user?.name ?? '-' }}
      — {{ req.status }}
    </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['bookId'],

  data() {
    return {
      book: {},
    };
  },

  mounted() {
    this.fetchBook();
  },

  methods: {
    fetchBook() {
      window.axios.get(`/api/books/${this.bookId}`).then((response) => {
        this.book = response.data;
      });
    },

    requisition() {
      window.axios
        .post('/api/requisitions', { book_id: this.bookId })
        .then(() => {
          this.fetchBook();
        })
        .catch((e) => {
          const msg = e.response?.data?.message || 'Não foi possível requisitar.';
          alert(msg);
        });
    },
  },
};
</script>
