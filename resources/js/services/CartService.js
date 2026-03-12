/**
 * Carrinho: fonte única no frontend (localStorage).
 * Backend entra apenas no checkout.
 */
const CART_KEY = 'library_cart';

export const CartService = {
  getCart() {
    try {
      const cart = window.localStorage.getItem(CART_KEY);
      return cart ? JSON.parse(cart) : [];
    } catch {
      return [];
    }
  },

  saveCart(cart) {
    window.localStorage.setItem(CART_KEY, JSON.stringify(cart));
    window.dispatchEvent(new CustomEvent('cart-updated'));
  },

  /**
   * Adiciona um livro ao carrinho. Valida stock.
   * @returns {{ success: boolean, message?: string }}
   */
  add(book) {
    const stock = (book.available_stock ?? book.stock) != null ? Number(book.available_stock ?? book.stock) : null;
    if (stock !== null && stock <= 0) {
      return { success: false, message: 'Esgotado' };
    }

    const cart = this.getCart();
    const title = book.title ?? book.name;
    const price = typeof book.price === 'number' ? book.price : parseFloat(book.price) || 0;
    const discount = typeof book.discount === 'number' ? book.discount : parseFloat(book.discount) || 0;
    const existing = cart.find((i) => i.book_id === book.id);

    if (existing) {
      const nextQty = (existing.quantity || 1) + 1;
      if (stock !== null && nextQty > stock) {
        return { success: false, message: 'Stock insuficiente' };
      }
      existing.quantity = nextQty;
      existing.discount = discount;
      if (stock != null) existing.stock = stock;
      if (book.available_stock != null) existing.available_stock = Number(book.available_stock);
    } else {
      cart.push({
        book_id: book.id,
        title: title ?? '—',
        price,
        discount,
        cover: book.cover_url ?? book.cover ?? null,
        quantity: 1,
        stock: stock ?? null,
        available_stock: book.available_stock != null ? Number(book.available_stock) : null,
      });
    }

    this.saveCart(cart);
    return { success: true };
  },

  remove(bookId) {
    const cart = this.getCart().filter((i) => i.book_id !== bookId);
    this.saveCart(cart);
  },

  updateQuantity(bookId, quantity) {
    if (quantity < 1) {
      this.remove(bookId);
      return;
    }
    const cart = this.getCart();
    const item = cart.find((i) => i.book_id === bookId);
    if (item) {
      const maxQty = (item.available_stock ?? item.stock) != null ? Number(item.available_stock ?? item.stock) : null;
      const qty = maxQty != null && quantity > maxQty ? maxQty : quantity;
      item.quantity = qty;
      this.saveCart(cart);
    }
  },

  clear() {
    window.localStorage.removeItem(CART_KEY);
    window.dispatchEvent(new CustomEvent('cart-updated'));
  },

  totalItems() {
    return this.getCart().reduce((sum, i) => sum + (i.quantity || 1), 0);
  },

  totalAmount() {
    return this.getCart().reduce((sum, i) => sum + (i.price || 0) * (i.quantity || 1), 0);
  },
};
