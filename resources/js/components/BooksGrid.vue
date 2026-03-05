<template>
  <div
    class="flex flex-row flex-nowrap gap-4 overflow-x-auto overflow-y-hidden pb-4 scroll-smooth snap-x snap-mandatory"
  >
    <div
      v-for="b in books"
      :key="b.id"
      class="w-[140px] min-w-[140px] max-w-[140px] shrink-0 snap-start flex flex-col"
    >
      <div class="bg-base-200 rounded-lg overflow-hidden shadow w-full">
        <div class="aspect-[2/3] w-full block">
          <img
            v-if="b.cover_url"
            :src="b.cover_url"
            :alt="b.name"
            class="w-full h-full object-cover block"
            loading="lazy"
          />
          <div
            v-else
            class="w-full h-full flex items-center justify-center text-xs text-base-content/50"
          >
            Sem capa
          </div>
        </div>
      </div>

      <h3 class="mt-2 text-sm font-semibold leading-tight line-clamp-2 min-w-0">
        <a
          v-if="isLogged"
          :href="`/books/${b.id}`"
          class="link link-primary"
        >
          {{ b.name }}
        </a>
        <span v-else class="text-base-content">
          {{ b.name }}
        </span>
      </h3>

      <p class="text-xs text-base-content/60 line-clamp-1 min-w-0">
        {{ b.authors?.map(a => a.name).join(', ') || '—' }}
      </p>

      <div class="mt-2 flex-shrink-0">
        <button
          v-if="b.is_available"
          @click="handleRequisition(b.id)"
          class="btn btn-xs btn-success w-full"
        >
          Requisitar
        </button>

        <span
          v-else-if="!b.is_available"
          class="badge badge-error badge-sm w-full justify-center"
        >
          Indisponível
        </span>

        
      </div>
    </div>    
  </div>
</template>

<script setup>
const props = defineProps({
  books: { type: Array, default: () => [] },
  isLogged: { type: Boolean, default: false },
});

const emit = defineEmits(['requisition']);

function handleRequisition(bookId) {
  if (!props.isLogged) {
    window.location.href = '/login';
    return;
  }

  emit('requisition', bookId);
}
</script>
