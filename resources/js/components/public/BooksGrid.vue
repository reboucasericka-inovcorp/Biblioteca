<template>
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <div
      v-for="b in books"
      :key="b.id"
      class="group relative bg-white rounded-xl shadow hover:shadow-lg transition-all duration-300 p-3 border border-base-200 hover:border-base-300"
    >
      <!-- badge desconto -->
      <div
        v-if="displayDiscount(b) > 0"
        class="absolute top-2 left-2 z-10 bg-base-content/90 text-primary-content text-xs font-semibold px-2 py-1 rounded"
      >
        -{{ displayDiscount(b) }}%
      </div>

      <!-- favorito -->
      <button
        type="button"
        class="absolute top-2 right-2 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-white/90 hover:bg-white shadow-sm opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity duration-200"
        :class="{ 'opacity-100 text-error': isFavorite(b.id) }"
        :aria-label="isFavorite(b.id) ? 'Remover dos favoritos' : 'Marcar como favorito'"
        @click="toggleFavorite(b.id, b)"
      >
        <span class="text-lg">{{ isFavorite(b.id) ? '♥' : '♡' }}</span>
      </button>

      <!-- capa (sempre link para /books/{id}) -->
      <a
        :href="`/books/${b.id}`"
        class="block rounded-lg overflow-hidden bg-base-200 aspect-[2/3] w-full"
      >
        <img
          :src="cover(b)"
          :alt="bookTitle(b)"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          loading="lazy"
          @error="(e) => (e.target.src = coverPlaceholder)"
        />
      </a>

      <!-- info -->
      <div class="mt-3 text-center min-h-[4.5rem]">
        <p class="text-xs text-base-content/60 line-clamp-1">
          {{ authorLabel(b) }}
        </p>
        <h3 class="text-sm font-semibold line-clamp-2 mt-0.5">
          <a :href="`/books/${b.id}`" class="link link-hover text-base-content">
            {{ bookTitle(b) }}
          </a>
        </h3>
        <template v-if="hasPrice(b)">
          <p v-if="displayDiscount(b) > 0" class="text-xs line-through text-base-content/40 mt-1">
            {{ formatPrice(originalPrice(b)) }}
          </p>
          <p class="text-lg font-bold text-primary mt-0.5">
            {{ formatPrice(discountPrice(b)) }}
          </p>
        </template>
        <template v-else>
          <p class="text-sm text-base-content/60 mt-1">—</p>
        </template>
      </div>

      <!-- disponibilidade (sem mostrar quantidade de stock na página pública) -->
      <div class="mt-2 flex flex-col items-center gap-1">
        <template v-if="isAvailable(b)">
          <p class="text-xs text-success">Disponível</p>
          <div class="w-full flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <button
              type="button"
              class="w-full py-2 px-4 rounded-lg bg-primary text-primary-content font-medium text-sm hover:bg-primary-focus"
              @click="addToCart(b)"
            >
              Adicionar ao carrinho
            </button>
            <button
              type="button"
              class="w-full py-2 px-4 rounded-lg border border-primary text-primary font-medium text-sm hover:bg-primary/5"
              @click="requisition(b)"
            >
              Requisitar livro
            </button>
          </div>
        </template>
        <template v-else>
          <span class="badge badge-error badge-sm">Esgotado</span>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { CartService } from '../../services/CartService.js';
import { useCartStore } from '../../stores/cartStore.js';
import { useFavoritesStore } from '../../stores/favoritesStore.js';

const props = defineProps({
  books: { type: Array, default: () => [] },
  isLogged: { type: Boolean, default: false },
});

const emit = defineEmits(['requisition', 'add-to-cart']);

const cartStore = useCartStore();
const favoritesStore = useFavoritesStore();

function loadFavorites() {
  if (!props.isLogged) return;
  favoritesStore.loadFavorites();
}

onMounted(loadFavorites);
watch(() => props.isLogged, loadFavorites);

const coverPlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='300' viewBox='0 0 200 300'%3E%3Crect fill='%23e5e7eb' width='200' height='300'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%239ca3af' font-size='14' font-family='sans-serif'%3ESem capa%3C/text%3E%3C/svg%3E";

function safe(obj, field, fallback = '—') {
  if (obj == null) return fallback;
  const val = obj[field];
  return val !== undefined && val !== null && val !== '' ? val : fallback;
}

function bookTitle(book) {
  return book?.title ?? book?.name ?? '—';
}

function cover(book) {
  const url = book?.cover_url ?? book?.thumbnail_url;
  return url && String(url).trim() ? url : coverPlaceholder;
}

function authorLabel(book) {
  if (book.authors?.length) {
    return book.authors.map((a) => a.name).join(', ');
  }
  return book.author ?? '—';
}

function displayDiscount(book) {
  if (typeof book.discount === 'number' && book.discount >= 0) {
    return Math.round(book.discount);
  }
  return 20;
}

function hasPrice(book) {
  const p = book.price ?? book.price_cents;
  if (p == null || p === '') return false;
  const n = typeof p === 'number' ? p : Number(p);
  return !Number.isNaN(n) && n >= 0;
}

function originalPrice(book) {
  const p = book.price ?? book.price_cents;
  if (p == null) return 0;
  const n = typeof p === 'number' ? p : Number(p);
  if (Number.isNaN(n)) return 0;
  return book.price_cents != null && typeof book.price_cents === 'number' ? n / 100 : n;
}

// Preço com desconto: se API não enviar discount, usa 20% (price * 0.8)
function discountPrice(book) {
  const orig = originalPrice(book);
  const d = displayDiscount(book) / 100;
  const value = orig * (1 - d);
  return Number(value.toFixed(2));
}

function formatPrice(value) {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number(value));
}

function isFavorite(bookId) {
  return favoritesStore.isFavorite(bookId);
}

async function toggleFavorite(bookId, book = null) {
  if (!props.isLogged) {
    window.location.href = '/login';
    return;
  }
  const wasFavorite = favoritesStore.isFavorite(bookId);
  const action = wasFavorite ? favoritesStore.removeFavorite(bookId) : favoritesStore.addFavorite(bookId, book);
  const result = await action;
  if (result?.success && window.showToast) {
    await favoritesStore.loadFavorites();
    window.showToast(wasFavorite ? 'Removido dos favoritos' : 'Adicionado aos favoritos', 'success');
  } else if (!result?.success && window.showToast) {
    window.showToast(wasFavorite ? 'Erro ao remover favorito' : 'Erro ao adicionar favorito', 'error');
  }
}

/** Disponível para compra (API pode enviar `available` para clientes ou `available_stock` para admin). */
function isAvailable(book) {
  if (book.available === true || book.available === false) return book.available === true;
  const s = book.available_stock ?? book.stock;
  if (s === undefined || s === null) return true;
  return Math.max(0, Number(s)) > 0;
}

function addToCart(book) {
  const result = CartService.add(book);
  if (!result.success) {
    if (window.showToast) window.showToast(result.message ?? 'Stock insuficiente', 'error');
    return;
  }
  cartStore.syncCartCount();
  if (window.showToast) window.showToast('Livro adicionado ao carrinho.', 'success');
  emit('add-to-cart', book);
}

function requisition(book) {
  if (document.body.dataset.auth !== '1') {
    window.location.href = '/login';
    return;
  }

  window.axios
    .post('/api/requisitions', { book_id: book.id })
    .then(() => {
      if (window.showToast) window.showToast('Requisição registada.', 'success');
      emit('requisition', book.id);
    })
    .catch((e) => {
      const msg = e.response?.data?.message || 'Não foi possível requisitar.';
      if (window.showToast) window.showToast(msg, 'error');
    });
}
</script>
