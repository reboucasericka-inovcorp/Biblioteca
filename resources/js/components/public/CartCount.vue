<template>
  <span
    class="bg-[#1e40af] text-white rounded-full px-2 text-xs min-w-[1.25rem] text-center font-semibold"
    :aria-label="`${store.cartCount} itens no carrinho`"
  >
    ({{ store.cartCount }})
  </span>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useCartStore } from '../../stores/cartStore.js';

const store = useCartStore();

function syncAndPing() {
  store.syncCartCount();
  if (document.body?.dataset?.auth === '1') {
    window.axios?.post('/api/cart/activity').catch(() => {});
  }
}

onMounted(() => {
  syncAndPing();
  window.addEventListener('cart-updated', syncAndPing);
});

onUnmounted(() => {
  window.removeEventListener('cart-updated', syncAndPing);
});
</script>
