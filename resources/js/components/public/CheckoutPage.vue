<template>
  <div class="max-w-[1700px] mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-base-content mb-6">Checkout</h1>

    <div v-if="cart.length === 0" class="text-center py-16 text-base-content/60">
      <p class="text-lg">O seu carrinho está vazio.</p>
      <a href="/cart" class="btn btn-primary mt-4">Ver carrinho</a>
    </div>

    <template v-else>
      <!-- Resumo da compra -->
      <div class="card bg-base-100 shadow mb-8">
        <div class="card-body">
          <h2 class="card-title text-lg mb-4">Resumo da compra</h2>
          <div class="overflow-x-auto">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Produto</th>
                  <th class="text-right">Preço</th>
                  <th class="text-center">Qtd</th>
                  <th class="text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in cart" :key="item.book_id">
                  <td>
                    <a :href="`/books/${item.book_id}`" class="link link-hover font-medium">{{ item.title }}</a>
                  </td>
                  <td class="text-right">{{ formatPrice(item.price) }}</td>
                  <td class="text-center">{{ item.quantity }}</td>
                  <td class="text-right">{{ formatPrice((item.price || 0) * (item.quantity || 1)) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="divider my-2"></div>

          <div class="flex flex-col items-end gap-1 text-right">
            <p class="text-base-content/80">
              Subtotal: <span class="font-medium">{{ formatPrice(subtotal) }}</span>
            </p>
            <p v-if="discountAmount > 0" class="text-success">
              Desconto: −{{ formatPrice(discountAmount) }}
            </p>
            <p class="text-lg font-bold text-base-content mt-2">
              Total: {{ formatPrice(totalAmount) }}
            </p>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-4">
        <a href="/cart" class="btn btn-ghost">← Voltar ao carrinho</a>
        <button
          type="button"
          class="btn btn-primary"
          :disabled="paying"
          @click="pay"
        >
          <span v-if="paying" class="loading loading-spinner loading-sm"></span>
          <span v-else>Pagar com Stripe</span>
        </button>
      </div>

      <p v-if="error" class="mt-4 text-error text-sm">{{ error }}</p>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { CartService } from '../../services/CartService.js';

const cart = ref([]);
const paying = ref(false);
const error = ref('');

const subtotal = computed(() => {
  return cart.value.reduce((sum, i) => sum + (i.price || 0) * (i.quantity || 1), 0);
});

const discountAmount = computed(() => 0);

const totalAmount = computed(() => subtotal.value);

function formatPrice(value) {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number(value));
}

function refreshCart() {
  cart.value = CartService.getCart();
}

async function pay() {
  if (cart.value.length === 0) return;
  error.value = '';
  paying.value = true;
  try {
    const payload = {
      items: cart.value.map((i) => ({
        book_id: i.book_id,
        quantity: i.quantity || 1,
      })),
    };
    const res = await window.axios.post('/api/checkout', payload);
    const data = res.data?.data;
    const url = data?.url;
    if (url) {
      window.location.href = url;
      return;
    }
    error.value = res.data?.message ?? 'Resposta inválida do servidor.';
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? 'Erro ao iniciar pagamento.';
    error.value = msg;
    if (window.showToast) window.showToast(msg, 'error');
  } finally {
    paying.value = false;
  }
}

onMounted(refreshCart);
</script>
