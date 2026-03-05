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
    </div>

    <div
      v-if="showUnavailableCard"
      class="rounded-2xl border border-error/30 bg-gradient-to-r from-error/10 to-base-100 p-4 sm:p-5 shadow-sm space-y-4"
    >
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 shrink-0 rounded-full bg-error/20 text-error flex items-center justify-center text-lg">
          🔴
        </div>
        <div class="min-w-0">
          <p class="text-base font-semibold text-error">Livro indisponível</p>
          <p class="text-sm text-base-content/80">Este livro está atualmente requisitado.</p>
        </div>
      </div>

      <button
        v-if="book.can_subscribe_availability_alert && !book.has_pending_availability_alert"
        @click="subscribeAvailabilityAlert"
        class="btn btn-sm btn-error text-white w-full sm:w-auto"
      >
        🔔 Notificar quando estiver disponível
      </button>

      <div
        v-if="book.can_subscribe_availability_alert && book.has_pending_availability_alert"
        class="alert alert-success py-2"
      >
        <span class="text-sm">✔ Será notificado quando o livro estiver disponível.</span>
      </div>
    </div>

    <div
      v-if="showAvailableAgainCard"
      class="rounded-2xl border border-success/30 bg-gradient-to-r from-success/10 to-base-100 p-4 sm:p-5 shadow-sm space-y-2"
    >
      <p class="text-base font-semibold text-success">📚 Livro voltou a estar disponível</p>
      <p class="text-sm text-base-content/80">Já pode requisitar este livro novamente.</p>
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

    <h4 class="text-lg font-semibold text-base-content pt-4">
      Reviews
    </h4>

    <div
      v-if="book.reviews?.length"
      v-for="review in book.reviews"
      :key="review.id"
      class="text-sm text-base-content border-b border-base-300 py-4"
    >
      <strong>{{ review.user?.name ?? 'Utilizador' }}</strong>
      — {{ review.rating }}/5
      <p class="mt-1">{{ review.comment }}</p>
    </div>
    <p v-else class="text-sm text-base-content/70">Sem reviews ativas para este livro.</p>

    <h4 class="text-lg font-semibold text-base-content pt-4">
      📚 Livros relacionados
    </h4>

    <div v-if="book.related_books?.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <a
        v-for="related in book.related_books"
        :key="related.id"
        :href="`/books/${related.id}`"
        class="group flex items-center gap-3 p-3 rounded-xl border border-base-300 bg-base-100 hover:shadow-md hover:-translate-y-0.5 transition"
      >
        <img
          v-if="related.cover_url"
          :src="related.cover_url"
          :alt="related.title"
          class="w-14 h-20 object-cover rounded-lg shadow-sm"
        >
        <div class="min-w-0">
          <p class="font-medium truncate group-hover:text-primary">{{ related.title }}</p>
          <p class="text-sm text-base-content/70 truncate">{{ related.author }}</p>
        </div>
      </a>
    </div>
    <p v-else class="text-sm text-base-content/70">Sem livros relacionados no momento.</p>
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

    subscribeAvailabilityAlert() {
      window.axios
        .post(`/api/books/${this.bookId}/alerts`)
        .then(() => {
          this.book.has_pending_availability_alert = true;
          this.book.has_subscribed_availability_alert = true;
          window.showToast?.('Será notificado quando o livro estiver disponível.', 'success');
        })
        .catch((error) => {
          const message = error.response?.data?.message || 'Não foi possível criar o alerta.';
          window.showToast?.(message, 'error');
        });
    },
  },

  computed: {
    showUnavailableCard() {
      return !this.book.is_available;
    },

    showAvailableAgainCard() {
      return Boolean(
        this.book.is_available
          && this.book.can_subscribe_availability_alert
          && this.book.has_subscribed_availability_alert
          && !this.book.has_pending_availability_alert
      );
    },
  },
};
</script>
