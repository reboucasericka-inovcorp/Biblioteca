<template>
  <div class="max-w-4xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

    <!-- Empty cart -->
    <div v-if="cart.length === 0" class="text-center py-16 text-gray-500">
      <p class="text-lg">O seu carrinho está vazio.</p>
      <a
        href="/cart"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 mt-4 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Ver carrinho
      </a>
    </div>

    <template v-else>
      <!-- Step indicator: Cart → Shipping → Payment -->
      <div class="flex items-center justify-between mb-8 text-sm">
        <div class="flex items-center flex-1">
          <span
            :class="[
              'font-medium',
              step === 1 ? 'text-blue-600' : 'text-gray-400',
            ]"
          >
            1. Carrinho
          </span>
        </div>
        <div class="flex-1 h-0.5 mx-2 bg-gray-200" aria-hidden="true" />
        <div class="flex items-center flex-1">
          <span
            :class="[
              'font-medium',
              step === 2 ? 'text-blue-600' : 'text-gray-400',
            ]"
          >
            2. Entrega
          </span>
        </div>
        <div class="flex-1 h-0.5 mx-2 bg-gray-200" aria-hidden="true" />
        <div class="flex items-center flex-1">
          <span
            :class="[
              'font-medium',
              step === 3 ? 'text-blue-600' : 'text-gray-400',
            ]"
          >
            3. Pagamento
          </span>
        </div>
      </div>

      <!-- Card: only current step content -->
      <div class="bg-white shadow rounded-lg p-6">
        <!-- Step 1: Resumo do carrinho -->
        <section v-show="step === 1">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumo do carrinho</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-3 font-medium text-gray-700">Produto</th>
                  <th class="text-right py-3 font-medium text-gray-700">Preço</th>
                  <th class="text-center py-3 font-medium text-gray-700">Quantidade</th>
                  <th class="text-right py-3 font-medium text-gray-700">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in cart"
                  :key="item.book_id"
                  class="border-b border-gray-100"
                >
                  <td class="py-3">
                    <a
                      :href="`/books/${item.book_id}`"
                      class="text-blue-600 hover:underline font-medium"
                    >
                      {{ item.title }}
                    </a>
                  </td>
                  <td class="text-right py-3 text-gray-700">
                    <span v-if="itemDiscount(item) > 0" class="line-through text-gray-500 text-xs mr-1">{{ formatPrice(itemPriceOriginal(item)) }}</span>
                    {{ formatPrice(itemPriceFinal(item)) }}
                    <span v-if="itemDiscount(item) > 0" class="text-green-600 text-xs ml-1">(−{{ itemDiscount(item) }}%)</span>
                  </td>
                  <td class="text-center py-3 text-gray-700">{{ item.quantity }}</td>
                  <td class="text-right py-3 font-medium text-gray-900">
                    {{ formatPrice(itemPriceFinal(item) * (item.quantity || 1)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex flex-col items-end gap-1 mt-4 pt-4 border-t border-gray-200 text-sm">
            <p class="flex justify-between w-full max-w-xs"><span class="text-gray-600">Subtotal</span><span>{{ formatPrice(subtotal) }}</span></p>
            <p class="flex justify-between w-full max-w-xs"><span class="text-gray-600">Desconto total</span><span class="text-green-600">− {{ formatPrice(totalSavings) }}</span></p>
            <p class="flex justify-between w-full max-w-xs"><span class="text-gray-600">Portes de envio</span><span class="text-green-600">Portes gratuitos</span></p>
            <p class="flex justify-between w-full max-w-xs font-semibold text-base mt-1">
              Total final <span class="text-gray-900">{{ formatPrice(totalAmount) }}</span>
            </p>
          </div>
          <div class="flex flex-wrap gap-3 justify-end mt-6">
            <a
              href="/cart"
              class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              ← Voltar ao carrinho
            </a>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              @click="step = 2"
            >
              Continuar → Entrega
            </button>
          </div>
        </section>

        <!-- Step 2: Morada de entrega + resumo -->
        <section v-show="step === 2">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Morada de entrega</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-4">
              <div>
                <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">
                  Morada
                </label>
                <input
                  id="shipping_address"
                  v-model="shipping.shipping_address"
                  type="text"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-base focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  placeholder="Rua, número, andar"
                />
                <p v-if="errors.shipping_address" class="mt-1 text-sm text-red-600">
                  {{ errors.shipping_address }}
                </p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-1">
                    Cidade
                  </label>
                  <input
                    id="shipping_city"
                    v-model="shipping.shipping_city"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-base focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Lisboa"
                  />
                  <p v-if="errors.shipping_city" class="mt-1 text-sm text-red-600">
                    {{ errors.shipping_city }}
                  </p>
                </div>
                <div>
                  <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                    Código postal
                  </label>
                  <input
                    id="shipping_postal_code"
                    v-model="shipping.shipping_postal_code"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-base focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="1000-001"
                  />
                  <p v-if="errors.shipping_postal_code" class="mt-1 text-sm text-red-600">
                    {{ errors.shipping_postal_code }}
                  </p>
                </div>
              </div>
              <div>
                <label for="shipping_country" class="block text-sm font-medium text-gray-700 mb-1">
                  País
                </label>
                <select
                  id="shipping_country"
                  v-model="shipping.shipping_country"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-base focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                  <option v-for="c in countryOptions" :key="c.code" :value="c.code">{{ c.name }}</option>
                </select>
                <p v-if="errors.shipping_country" class="mt-1 text-sm text-red-600">
                  {{ errors.shipping_country }}
                </p>
              </div>
            </div>
            <div class="md:col-span-1">
              <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Resumo do pedido</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                  <li v-for="item in cart" :key="item.book_id" class="flex justify-between">
                    <span class="truncate max-w-[120px]">{{ item.title }}</span>
                    <span class="font-medium text-gray-900">{{ item.quantity }} × {{ formatPrice(itemPriceFinal(item)) }}</span>
                  </li>
                </ul>
                <div class="mt-3 pt-3 border-t border-gray-200 space-y-1 text-sm">
                  <p class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>{{ formatPrice(subtotal) }}</span></p>
                  <p class="flex justify-between"><span class="text-gray-600">Desconto</span><span class="text-green-600">− {{ formatPrice(totalSavings) }}</span></p>
                  <p class="flex justify-between"><span class="text-gray-600">Portes</span><span class="text-green-600">Portes gratuitos</span></p>
                  <p class="flex justify-between font-semibold text-gray-900 pt-1">
                    Total <span>{{ formatPrice(totalAmount) }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap gap-3 justify-end mt-6">
            <button
              type="button"
              class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              @click="step = 1"
            >
              ← Voltar
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              @click="step = 3"
            >
              Continuar → Pagamento
            </button>
          </div>
        </section>

        <!-- Step 3: Pagamento -->
        <section v-show="step === 3">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Pagamento</h2>
          <div class="flex flex-col items-end">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 w-full max-w-sm">
              <p class="text-sm text-gray-600 mb-1">Total do pedido</p>
              <p class="text-2xl font-bold text-gray-900 mb-6">{{ formatPrice(totalAmount) }}</p>
              <button
                type="button"
                class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="paying"
                @click="pay"
              >
                <span v-if="paying" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent mr-2" />
                {{ paying ? 'A processar...' : 'Pagar com Stripe' }}
              </button>
            </div>
            <div class="flex justify-end mt-4 w-full max-w-sm">
              <button
                type="button"
                class="text-sm text-gray-600 hover:text-gray-900"
                @click="step = 2"
              >
                ← Alterar morada
              </button>
            </div>
          </div>
        </section>
      </div>

      <p v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</p>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { CartService } from '../../services/CartService.js';

const cart = ref([]);
const step = ref(1);
const paying = ref(false);
const error = ref('');

const shipping = reactive({
  shipping_address: '',
  shipping_city: '',
  shipping_postal_code: '',
  shipping_country: 'PT',
});

const errors = reactive({
  shipping_address: '',
  shipping_city: '',
  shipping_postal_code: '',
  shipping_country: '',
});

const countryOptions = [
  { code: 'PT', name: 'Portugal' },
  { code: 'ES', name: 'Espanha' },
  { code: 'FR', name: 'França' },
  { code: 'DE', name: 'Alemanha' },
  { code: 'IT', name: 'Itália' },
  { code: 'GB', name: 'Reino Unido' },
  { code: 'BR', name: 'Brasil' },
  { code: 'AO', name: 'Angola' },
  { code: 'MZ', name: 'Moçambique' },
];

function itemDiscount(item) {
  return Number(item.discount ?? 0);
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

function formatPrice(value) {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number(value));
}

function refreshCart() {
  cart.value = CartService.getCart();
}

function validateShipping() {
  let valid = true;
  errors.shipping_address = '';
  errors.shipping_city = '';
  errors.shipping_postal_code = '';
  errors.shipping_country = '';

  if (!shipping.shipping_address?.trim()) {
    errors.shipping_address = 'Morada é obrigatória.';
    valid = false;
  }
  if (!shipping.shipping_city?.trim()) {
    errors.shipping_city = 'Cidade é obrigatória.';
    valid = false;
  }
  if (!shipping.shipping_postal_code?.trim()) {
    errors.shipping_postal_code = 'Código postal é obrigatório.';
    valid = false;
  }
  const country = (shipping.shipping_country || '').trim().toUpperCase();
  if (country.length !== 2) {
    errors.shipping_country = 'Selecione o país.';
    valid = false;
  }
  return valid;
}

async function pay() {
  if (cart.value.length === 0) return;
  if (!validateShipping()) return;

  error.value = '';
  paying.value = true;
  try {
    const payload = {
      items: cart.value.map((i) => ({
        book_id: i.book_id,
        quantity: i.quantity || 1,
      })),
      shipping_address: shipping.shipping_address.trim(),
      shipping_city: shipping.shipping_city.trim(),
      shipping_postal_code: shipping.shipping_postal_code.trim(),
      shipping_country: shipping.shipping_country.trim().toUpperCase(),
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
