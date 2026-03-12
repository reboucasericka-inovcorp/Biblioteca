import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),

  getters: {
    isAuthenticated(state) {
      return state.user != null;
    },
  },

  actions: {
    setUser(user) {
      this.user = user;
    },
  },
});
