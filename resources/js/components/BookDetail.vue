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

    <div class="flex flex-wrap gap-2">
      <button
        v-if="book.is_available"
        @click="requisition"
        class="btn btn-primary"
      >
        Requisitar
      </button>

      <button
        v-if="book.can_download && book.has_pdf"
        @click="download"
        class="btn btn-primary"
      >
        Baixar Livro
      </button>
      <button
        v-else-if="book.can_download && book.google_volume_id"
        @click="openPreview"
        class="btn btn-outline"
      >
        Ler no Google Books
      </button>

      <span v-if="!book.is_available && !book.can_download" class="badge badge-error">
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
import { unwrap } from '../api';

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
      window.axios.get(`/api/books/${this.bookId}`, { withCredentials: true }).then((response) => {
        this.book = unwrap(response) ?? {};
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
          window.showToast(msg, 'error');
        });
    },

    download() {
      window.location.href = `/books/${this.bookId}/download`;
    },

    openPreview() {
      if (this.book.google_volume_id) {
        window.open(`https://books.google.com/books?id=${this.book.google_volume_id}`, '_blank');
      }
    },
  },
};
</script>
