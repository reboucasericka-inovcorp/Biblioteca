<template>
  <div ref="dropdownRoot" class="contents">
    <!-- Guest: Login + Criar Conta -->
    <div v-if="!auth" class="text-right leading-tight flex items-center gap-4">
      <a :href="loginUrl" class="block hover:underline">Login</a>
      <a v-if="hasRegister" :href="registerUrl" class="block hover:underline">Criar Conta</a>
    </div>

    <!-- Auth: dropdown trigger + content -->
    <div v-else class="relative">
      <button
        type="button"
        class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-white/50 transition"
        :class="userPhoto ? '' : 'inline-flex items-center px-3 py-2 font-medium text-white/90 hover:text-white'"
        @click="open = !open"
        aria-haspopup="true"
        :aria-expanded="open"
      >
        <img
          v-if="userPhoto"
          class="size-8 rounded-full object-cover"
          :src="userPhoto"
          :alt="userName"
        />
        <template v-else>
          <span>{{ userName || 'Conta' }}</span>
          <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
          </svg>
        </template>
      </button>

      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-show="open"
          class="absolute z-50 mt-2 w-72 end-0 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-800"
          role="menu"
        >
          <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <img
              v-if="userPhoto"
              class="size-10 shrink-0 rounded-full object-cover"
              :src="userPhoto"
              :alt="userName"
            />
            <div class="min-w-0 flex-1">
              <div class="font-medium text-gray-900 dark:text-gray-100 break-words">{{ userName }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400 break-words">{{ userEmail }}</div>
            </div>
          </div>
          <a
            :href="profileUrl"
            class="block w-full px-4 py-2 text-start text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition"
            role="menuitem"
            @click="open = false"
          >
            Perfil
          </a>
          <form method="POST" :action="logoutUrl" class="block" @submit="open = false">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="block w-full px-4 py-2 text-start text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition"
              role="menuitem"
            >
              Sair
            </button>
          </form>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  auth: { type: Boolean, default: false },
  user: { type: Object, default: null },
  loginUrl: { type: String, default: '#' },
  registerUrl: { type: String, default: '' },
  profileUrl: { type: String, default: '#' },
  logoutUrl: { type: String, default: '#' },
  csrf: { type: String, default: '' },
  hasRegister: { type: Boolean, default: false },
});

const userName = computed(() => props.user?.name ?? '');
const userEmail = computed(() => props.user?.email ?? '');
const userPhoto = computed(() => props.user?.profile_photo_url ?? '');

const open = ref(false);
const dropdownRoot = ref(null);

function onClickOutside(e) {
  if (!dropdownRoot.value || !open.value) return;
  if (dropdownRoot.value.contains(e.target)) return;
  open.value = false;
}

onMounted(() => {
  document.addEventListener('click', onClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', onClickOutside);
});
</script>
