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
              <th class="text-right">Preço original</th>
              <th class="text-center w-24">Desconto</th>
              <th class="text-right">Preço final</th>
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
              <td class="text-right">
                <span v-if="itemDiscount(item) > 0" class="text-base-content/60 line-through text-sm">{{ formatPrice(itemPriceOriginal(item)) }}</span>
                <span v-else>{{ formatPrice(itemPriceOriginal(item)) }}</span>
              </td>
              <td class="text-center">
                <span v-if="itemDiscount(item) > 0" class="text-success font-medium">{{ itemDiscount(item) }}%</span>
                <span v-else class="text-base-content/40">—</span>
              </td>
              <td class="text-right font-medium">{{ formatPrice(itemPriceFinal(item)) }}</td>
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
              <td class="text-right font-medium">{{ formatPrice(itemPriceFinal(item) * item.quantity) }}</td>
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

      <!-- Resumo: Subtotal, Desconto total, Portes, Total -->
      <div class="mt-8 flex flex-col md:flex-row justify-between items-stretch md:items-end gap-6">
        <div class="card bg-base-200/50 rounded-lg p-4 max-w-sm w-full">
          <h3 class="font-semibold text-base-content mb-3">Resumo do carrinho</h3>
          <ul class="space-y-2 text-sm">
            <li class="flex justify-between">
              <span class="text-base-content/70">Subtotal</span>
              <span>{{ formatPrice(subtotal) }}</span>
            </li>
            <li class="flex justify-between">
              <span class="text-base-content/70">Desconto total</span>
              <span class="text-success font-medium">− {{ formatPrice(totalSavings) }}</span>
            </li>
            <li class="flex justify-between">
              <span class="text-base-content/70">Portes de envio</span>
              <span class="text-success font-medium">Portes gratuitos</span>
            </li>
            <li class="flex justify-between pt-2 border-t border-base-300 font-semibold text-base">
              <span>Total final</span>
              <span>{{ formatPrice(totalAmount) }}</span>
            </li>
          </ul>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
          <a href="/" class="btn btn-ghost btn-sm sm:btn-md">
            ← Continuar a comprar
          </a>
          <div class="flex gap-2">
            <button type="button" class="btn btn-outline" @click="clearCart">Esvaziar carrinho</button>
            <a href="/checkout" class="btn btn-primary">Checkout →</a>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { CartService } from '../../services/CartService.js';

const cart = ref([]);

function itemDiscount(item) {
  const d = item.discount ?? 0;
  return Number(d);
}

function itemPriceOriginal(item) {
  return Number(item.price ?? 0);
}

function itemPriceFinal(item) {
  const p = itemPriceOriginal(item);
  const d = itemDiscount(item) / 100;
  return Math.round(p * (1 - d) * 100) / 100;
}

const subtotal = computed(() =>
  cart.value.reduce((sum, i) => sum + itemPriceOriginal(i) * (i.quantity || 1), 0)
);

const totalAmount = computed(() =>
  cart.value.reduce((sum, i) => sum + itemPriceFinal(i) * (i.quantity || 1), 0)
);

const totalSavings = computed(() =>
  Math.round((subtotal.value - totalAmount.value) * 100) / 100
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
