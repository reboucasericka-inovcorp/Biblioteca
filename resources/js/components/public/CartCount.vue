<template>
  <span class="bg-[#1e40af] text-white rounded-full px-2 text-xs min-w-[1.25rem] text-center">
    {{ count }}
  </span>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { CartService } from '../../services/CartService.js';

const count = ref(0);

function updateCount() {
  count.value = CartService.totalItems();
}

function pingCartActivity() {
  if (document.body?.dataset?.auth !== '1') return;
  window.axios?.post('/api/cart/activity').catch(() => {});
}

onMounted(() => {
  updateCount();
  window.addEventListener('cart-updated', () => {
    updateCount();
    pingCartActivity();
  });
});

onUnmounted(() => {
  window.removeEventListener('cart-updated', updateCount);
});
</script>
