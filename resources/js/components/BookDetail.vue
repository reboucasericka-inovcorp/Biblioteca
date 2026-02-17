<template>
  <div class="bg-white p-6 rounded shadow">
    <h3 class="text-2xl font-bold mb-2">{{ book.name }}</h3>

    <img
      v-if="book.cover_url"
      :src="book.cover_url"
      class="w-40 mb-4 rounded shadow"
      :alt="book.name"
    />

    <p class="mb-4">{{ book.bibliography }}</p>

    <div class="mb-6">
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

    <h4 class="text-lg font-semibold mb-2">
      Histórico de Requisições
    </h4>

    <div
      v-for="req in book.requisitions"
      :key="req.id"
      class="text-sm border-b py-2"
    >
      <strong>#{{ req.sequential_number }}</strong>
      — {{ req.user?.name ?? '-' }}
      — {{ req.status }}
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
