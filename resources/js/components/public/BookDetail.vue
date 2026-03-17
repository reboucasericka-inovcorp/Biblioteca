<template>
  <div class="max-w-[1700px] mx-auto px-6 py-10">
    <div v-if="loading" class="flex justify-center py-20">
      <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    <template v-else-if="book.id">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna da imagem -->
        <div class="lg:col-span-1 space-y-4">
          <div class="relative bg-base-200 rounded-xl overflow-hidden shadow-lg aspect-[2/3] max-h-[500px]">
            <img
              :src="cover(book)"
              :alt="bookTitle"
              class="w-full h-full object-cover"
              @error="(e) => (e.target.src = coverPlaceholder)"
            />
            <!-- Badge desconto -->
            <div
              v-if="displayDiscount > 0"
              class="absolute top-3 left-3 bg-base-content text-primary-content text-sm font-semibold px-3 py-1.5 rounded"
            >
              -{{ displayDiscount }}%
            </div>
          </div>
        </div>

        <!-- Coluna da informação -->
        <div class="lg:col-span-2">
          <h1 class="text-3xl font-bold text-base-content mb-2">
            {{ bookTitle }}
          </h1>
          <p class="text-base-content/70 mb-4">
            {{ authorLabel }}
          </p>

          <!-- Preços (EUR pt-PT: 29,90 €) -->
          <div class="flex flex-wrap items-baseline gap-3 mb-6">
            <template v-if="hasPrice">
              <span v-if="displayDiscount > 0" class="text-lg text-base-content/50 line-through">
                {{ formatPrice(originalPrice) }}
              </span>
              <span class="text-2xl font-bold text-primary">
                {{ formatPrice(discountPrice) }}
              </span>
            </template>
            <span v-else class="text-base-content/60">Preço sob consulta</span>
          </div>

          <!-- Disponibilidade (sem mostrar quantidade de stock na página pública) -->
          <div class="mb-3">
            <p v-if="isAvailable" class="text-sm text-success">Disponível</p>
            <p v-else class="text-sm text-error font-medium">Esgotado</p>
          </div>

          <!-- Ações -->
          <div class="flex flex-wrap gap-3 mb-8">
            <button
              v-if="isAvailable"
              type="button"
              class="btn btn-primary"
              @click="addToCart"
            >
              Adicionar ao carrinho
            </button>
            <button
              v-if="book.is_available"
              type="button"
              class="btn btn-outline btn-primary"
              @click="requisition"
            >
              Comprar
            </button>
            <button
              type="button"
              class="btn btn-ghost gap-2"
              :class="{ 'text-error': isFavorite }"
              @click="toggleFavorite"
            >
              <span class="text-xl">{{ isFavorite ? '♥' : '♡' }}</span>
              Favoritar
            </button>
            <template v-if="!book.is_available">
              <span class="badge badge-error badge-lg">Indisponível (requisição)</span>
              <button
                v-if="book.can_subscribe_availability_alert && !book.has_pending_availability_alert"
                type="button"
                class="btn btn-sm btn-ghost"
                @click="subscribeAvailabilityAlert"
              >
                🔔 Notificar quando estiver disponível
              </button>
            </template>
          </div>

          <!-- Download / Google Books (biblioteca) -->
          <div class="flex flex-wrap gap-2 mb-6">
            <button
              v-if="book.can_download && book.has_pdf"
              type="button"
              class="btn btn-success btn-sm"
              @click="download"
            >
              Baixar Livro
            </button>
            <button
              v-else-if="book.can_download && book.google_volume_id"
              type="button"
              class="btn btn-outline btn-sm"
              @click="openPreview"
            >
              Ler no Google Books
            </button>
          </div>

          <!-- Informações técnicas -->
          <div class="card bg-base-200/50 rounded-xl shadow-sm mb-6">
            <div class="card-body p-6">
              <h3 class="card-title text-base mb-3">Informações técnicas</h3>
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                <template v-if="book.pages != null && book.pages !== ''">
                  <dt class="text-base-content/60">Páginas</dt>
                  <dd>{{ safe(book, 'pages', '—') }}</dd>
                </template>
                <template v-if="book.language">
                  <dt class="text-base-content/60">Idioma</dt>
                  <dd>{{ safe(book, 'language', '—') }}</dd>
                </template>
                <template v-if="book.isbn">
                  <dt class="text-base-content/60">ISBN</dt>
                  <dd class="font-mono">{{ safe(book, 'isbn', '—') }}</dd>
                </template>
                <template v-if="book.dimensions">
                  <dt class="text-base-content/60">Dimensões</dt>
                  <dd>{{ safe(book, 'dimensions', '—') }}</dd>
                </template>
                <template v-if="book.publisher?.name">
                  <dt class="text-base-content/60">Editora</dt>
                  <dd>{{ safe(book.publisher, 'name', '—') }}</dd>
                </template>
              </dl>
            </div>
          </div>

          <!-- Descrição -->
          <div class="card bg-base-100 rounded-xl shadow-sm border border-base-200">
            <div class="card-body p-6">
              <h3 class="card-title text-base mb-3">Descrição</h3>
              <p class="text-base-content whitespace-pre-wrap">{{ safe(book, 'bibliography', 'Sem descrição.') }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews (opcional) -->
      <section v-if="book.reviews?.length" class="mt-10 pt-8 border-t border-base-200">
        <h2 class="text-xl font-semibold mb-4">Reviews</h2>
        <div class="space-y-4">
          <div
            v-for="review in book.reviews"
            :key="review.id"
            class="p-4 rounded-lg bg-base-200/50 text-sm"
          >
            <p class="font-medium">{{ review.user?.name ?? 'Utilizador' }} — {{ review.rating }}/5</p>
            <p class="mt-1 text-base-content/80">{{ review.comment }}</p>
          </div>
        </div>
      </section>

      <!-- Livros relacionados -->
      <section v-if="book.related_books?.length" class="mt-10 pt-8 border-t border-base-200">
        <h2 class="text-xl font-semibold mb-4">Livros relacionados</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
          <a
            v-for="related in book.related_books"
            :key="related.id"
            :href="`/books/${related.id}`"
            class="group flex flex-col gap-2 p-3 rounded-xl border border-base-200 hover:shadow-md transition"
          >
            <img
              :src="cover(related)"
              :alt="relatedTitle(related)"
              class="w-full aspect-[2/3] object-cover rounded-lg"
              @error="(e) => (e.target.src = coverPlaceholder)"
            />
            <p class="font-medium text-sm line-clamp-2 group-hover:text-primary">{{ relatedTitle(related) }}</p>
            <p class="text-xs text-base-content/60">{{ safe(related, 'author', '—') }}</p>
          </a>
        </div>
      </section>
    </template>

    <div v-else class="text-center py-20 text-base-content/60">
      Livro não encontrado.
    </div>
  </div>
</template>

<script>
import { unwrap } from '../../api';
import { CartService } from '../../services/CartService.js';
import { useCartStore } from '../../stores/cartStore.js';
import { useFavoritesStore } from '../../stores/favoritesStore.js';

export default {
  props: {
    bookId: { type: [Number, String], required: true },
  },

  data() {
    return {
      book: {},
      loading: true,
      isFavorite: false,
      coverPlaceholder: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='300' viewBox='0 0 200 300'%3E%3Crect fill='%23e5e7eb' width='200' height='300'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%239ca3af' font-size='14' font-family='sans-serif'%3ESem capa%3C/text%3E%3C/svg%3E",
    };
  },

  computed: {
    authorLabel() {
      if (this.book.authors?.length) {
        return this.book.authors.map((a) => a.name).join(', ');
      }
      return this.book.author ?? '—';
    },

    displayDiscount() {
      const d = this.book.discount;
      if (typeof d === 'number' && d >= 0) return Math.round(d);
      return 20;
    },

    hasPrice() {
      const p = this.book.price;
      if (p == null || p === '') return false;
      const n = typeof p === 'number' ? p : Number(p);
      return !Number.isNaN(n) && n >= 0;
    },

    originalPrice() {
      const p = this.book.price;
      if (p == null) return 0;
      const n = typeof p === 'number' ? p : Number(p);
      return Number.isNaN(n) ? 0 : n;
    },

    discountPrice() {
      const orig = this.originalPrice;
      const d = this.displayDiscount / 100;
      return Number((orig * (1 - d)).toFixed(2));
    },

    bookTitle() {
      return this.book?.title ?? this.book?.name ?? '—';
    },

    /** Disponível para compra (API pode enviar `available` para clientes ou stock para admin). */
    isAvailable() {
      if (this.book?.available === true || this.book?.available === false) {
        return this.book.available === true;
      }
      const s = this.book?.available_stock ?? this.book?.stock;
      if (s === undefined || s === null) return true;
      return Math.max(0, Number(s)) > 0;
    },
  },

  mounted() {
    this.fetchBook();
  },

  methods: {
    safe(obj, field, fallback = '—') {
      if (obj == null) return fallback;
      const val = obj[field];
      return val !== undefined && val !== null && val !== '' ? val : fallback;
    },

    cover(book) {
      const url = book?.cover_url ?? book?.thumbnail_url;
      return url && String(url).trim() ? url : this.coverPlaceholder;
    },

    relatedTitle(related) {
      return related?.title ?? related?.name ?? '—';
    },

    formatPrice(value) {
      return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
      }).format(Number(value));
    },

    async fetchBook() {
      this.loading = true;
      try {
        const res = await window.axios.get(`/api/books/${this.bookId}`, { withCredentials: true });
        this.book = unwrap(res) ?? {};
        this.isFavorite = this.book.is_favorite === true;
      } catch (e) {
        this.book = {};
      } finally {
        this.loading = false;
      }
    },

    addToCart() {
      const result = CartService.add(this.book);
      if (!result.success) {
        if (window.showToast) window.showToast(result.message ?? 'Stock insuficiente', 'error');
        return;
      }
      useCartStore().syncCartCount();
      if (window.showToast) window.showToast('Livro adicionado ao carrinho.', 'success');
    },

    requisition() {
      if (document.body.dataset.auth !== '1') {
        window.location.href = '/login';
        return;
      }
      window.axios
        .post('/api/requisitions', { book_id: this.bookId })
        .then(() => {
          this.fetchBook();
          if (window.showToast) window.showToast('Requisição registada.', 'success');
        })
        .catch((e) => {
          const msg = e.response?.data?.message || 'Não foi possível requisitar.';
          if (window.showToast) window.showToast(msg, 'error');
        });
    },

    async toggleFavorite() {
      if (document.body.dataset.auth !== '1') {
        window.location.href = '/login';
        return;
      }
      const store = useFavoritesStore();
      const wasFavorite = this.isFavorite;
      const result = wasFavorite
        ? await store.removeFavorite(Number(this.bookId))
        : await store.addFavorite(Number(this.bookId), this.book);
      if (result?.success) {
        this.isFavorite = store.isFavorite(Number(this.bookId));
        if (window.showToast) {
          window.showToast(this.isFavorite ? 'Adicionado aos favoritos.' : 'Removido dos favoritos.', 'success');
        }
      } else {
        if (window.showToast) window.showToast('Não foi possível atualizar os favoritos.', 'error');
      }
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
          if (window.showToast) window.showToast('Será notificado quando estiver disponível.', 'success');
        })
        .catch((e) => {
          const msg = e.response?.data?.message || 'Não foi possível criar o alerta.';
          if (window.showToast) window.showToast(msg, 'error');
        });
    },
  },
};
</script>
