import { defineStore } from 'pinia';

export const useFavoritesStore = defineStore('favorites', {
  state: () => ({
    favorites: [],
    favoritesLoading: false,
  }),

  getters: {
    favoriteIds(state) {
      return state.favorites.map((b) => b.id).filter(Boolean);
    },
    isFavorite(state) {
      return (bookId) => state.favorites.some((b) => b.id === bookId);
    },
  },

  actions: {
    async loadFavorites() {
      if (document.body?.dataset?.auth !== '1') {
        this.favorites = [];
        return;
      }
      this.favoritesLoading = true;
      try {
        const res = await window.axios.get('/api/favorites', { withCredentials: true });
        const data = res.data?.data;
        this.favorites = Array.isArray(data) ? data : [];
      } catch {
        this.favorites = [];
      } finally {
        this.favoritesLoading = false;
      }
    },

    async addFavorite(bookId, book = null) {
      try {
        await window.axios.post(`/api/books/${bookId}/favorite`, {}, { withCredentials: true });
        if (!this.favorites.find((b) => b.id === bookId)) {
          this.favorites.push(book && typeof book === 'object' ? { ...book, id: book.id ?? bookId } : { id: bookId });
        }
        return { success: true };
      } catch {
        return { success: false };
      }
    },

    async removeFavorite(bookId) {
      try {
        await window.axios.delete(`/api/books/${bookId}/favorite`, { withCredentials: true });
        this.favorites = this.favorites.filter((b) => b.id !== bookId);
        return { success: true };
      } catch {
        return { success: false };
      }
    },
  },
});
