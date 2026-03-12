import { defineStore } from 'pinia';
import { CartService } from '../services/CartService.js';

export const useCartStore = defineStore('cart', {
  state: () => ({
    cartCount: 0,
  }),

  actions: {
    setCartCount(count) {
      this.cartCount = count;
    },

    syncCartCount() {
      this.cartCount = CartService.totalItems();
    },
  },
});
