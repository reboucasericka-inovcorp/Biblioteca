<template>
  <div class="max-w-[1700px] mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-base-content mb-6">Carrinho</h1>

    <div v-if="cart.length === 0" class="text-center py-16 text-base-content/60">
      <p class="text-lg">O seu carrinho está vazio.</p>
      <a href="/" class="btn btn-primary mt-4">Continuar a comprar</a>
    </div>

    <template v-else>
      <div class="overflow-x-auto">
        <table class="table table-zebra w-full">
          <thead>
            <tr>
              <th class="w-20">Capa</th>
              <th>Título</th>
              <th class="text-right">Preço</th>
              <th class="w-32 text-center">Quantidade</th>
              <th class="text-right">Subtotal</th>
              <th class="w-20"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in cart" :key="item.book_id">
              <td>
                <img
                  v-if="item.cover"
                  :src="item.cover"
                  :alt="item.title"
                  class="w-12 h-16 object-cover rounded"
                />
                <div v-else class="w-12 h-16 rounded bg-base-200 flex items-center justify-center text-xs text-base-content/40">—</div>
              </td>
              <td>
                <a :href="`/books/${item.book_id}`" class="link link-hover font-medium">{{ item.title }}</a>
              </td>
              <td class="text-right">{{ formatPrice(item.price) }}</td>
              <td class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    type="button"
                    class="btn btn-ghost btn-xs"
                    :disabled="item.quantity <= 1"
                    @click="changeQty(item.book_id, item.quantity - 1)"
                  >
                    −
                  </button>
                  <span class="w-8 text-center">{{ item.quantity }}</span>
                  <button
                    type="button"
                    class="btn btn-ghost btn-xs"
                    :disabled="isAtMaxStock(item)"
                    :title="isAtMaxStock(item) ? `Stock máximo: ${item.available_stock ?? item.stock}` : undefined"
                    @click="changeQty(item.book_id, item.quantity + 1)"
                  >
                    +
                  </button>
                </div>
              </td>
              <td class="text-right font-medium">{{ formatPrice(item.price * item.quantity) }}</td>
              <td>
                <button
                  type="button"
                  class="btn btn-ghost btn-xs text-error"
                  aria-label="Remover"
                  @click="remove(item.book_id)"
                >
                  🗑 Remover
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="text-lg font-semibold text-base-content">
          Total: {{ formatPrice(totalAmount) }}
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-ghost" @click="clearCart">Esvaziar carrinho</button>
          <a href="/" class="btn btn-outline">Continuar a comprar</a>
          <a href="/checkout" class="btn btn-primary">Checkout</a>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { CartService } from '../../services/CartService.js';

const cart = ref([]);

const totalAmount = computed(() =>
  cart.value.reduce((sum, i) => sum + (i.price || 0) * (i.quantity || 1), 0)
);

function refreshCart() {
  cart.value = CartService.getCart();
}

function formatPrice(value) {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number(value));
}

function isAtMaxStock(item) {
  const max = item.available_stock ?? item.stock;
  if (max == null) return false;
  return (item.quantity || 0) >= Number(max);
}

function changeQty(bookId, qty) {
  CartService.updateQuantity(bookId, qty);
  cart.value = CartService.getCart();
}

function remove(bookId) {
  CartService.remove(bookId);
  cart.value = CartService.getCart();
}

function clearCart() {
  CartService.clear();
  cart.value = [];
}

onMounted(() => {
  refreshCart();
  window.addEventListener('cart-updated', refreshCart);
});

onUnmounted(() => {
  window.removeEventListener('cart-updated', refreshCart);
});
</script>
