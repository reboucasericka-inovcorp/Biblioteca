import { defineStore } from 'pinia';


/**
 * Extrai a lista de livros da resposta GET /api/favorites.
 * Suporta ApiResponse + JsonResource (data aninhado), payload simples em data, ou array no corpo.
 */
function favoritesArrayFromResponse(res) {
  const root = res?.data ?? null;

  if (Array.isArray(root?.data?.data)) {
    return root.data.data;
  }

  if (Array.isArray(root?.data)) {
    return root.data;
  }

  if (Array.isArray(root)) {
    return root;
  }

  return [];
}

export const useFavoritesStore = defineStore('favorites', {
  state: () => ({
    favorites: [],
    loading: false,
    favoritesLoaded: false,
    favoritesRequest: null,
  }),

  getters: {
    hasFavorites(state) {
      return state.favorites.length > 0;
    },
    favoriteIds(state) {
      return state.favorites.map((b) => b.id).filter(Boolean);
    },
    isFavorite(state) {
      return (bookId) => state.favorites.some((b) => b.id === bookId);
    },
  },

  actions: {
    async loadFavorites(force = false) {
      console.log('LOAD FAVORITES START');
      if (this.loading) return;
      if (this.favoritesLoaded && !force) return;
      this.loading = true;
    
      try {
        if (document.body?.dataset?.auth !== '1') {
          console.log('NOT AUTH');
          this.favorites = [];
          this.favoritesLoaded = true;
          return;
        }

        const res = await window.axios.get('/api/favorites', { withCredentials: true });
    
        console.log('RAW RESPONSE:', res.data);
    
        const data = favoritesArrayFromResponse(res);
    
        console.log('FINAL ARRAY:', data);
    
        this.favorites = Array.isArray(data) ? data : [];
        this.favoritesLoaded = true;
    
        console.log('STORE UPDATED:', this.favorites);
    
      } catch (e) {
        console.error('ERROR:', e);
        this.favorites = [];
        this.favoritesLoaded = false;
      } finally {
        this.loading = false;
        this.favoritesLoaded = true;
      }
    },

    async addFavorite(bookId) {
      try {
        await window.axios.post(`/api/books/${bookId}/favorite`, {}, { withCredentials: true });
        await this.loadFavorites(true);
        return { success: true };
      } catch {
        return { success: false };
      }
    },

    async removeFavorite(bookId) {
      try {
        await window.axios.delete(`/api/books/${bookId}/favorite`, { withCredentials: true });
        await this.loadFavorites(true);
        return { success: true };
      } catch {
        return { success: false };
      }
    },
  },
});
