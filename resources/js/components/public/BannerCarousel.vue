<template>
  <section
    class="relative w-full overflow-hidden aspect-[21/9] max-h-80 bg-base-200"
    @mouseenter="pause"
    @mouseleave="startAutoplay"
  >
    <div
      ref="carousel"
      class="flex overflow-x-hidden scroll-smooth h-full"
    >
      <div
        v-for="(slide, i) in slides"
        :key="i"
        class="carousel-item min-w-full flex-shrink-0"
      >
        <img :src="slide.image" :alt="`Banner ${i + 1}`" class="w-full h-full object-cover">
      </div>
    </div>

    <!-- Prev -->
    <button
      type="button"
      @click="prev"
      class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/40 text-white px-3 py-2 rounded hover:bg-black/60 transition"
      aria-label="Anterior"
    >
      ‹
    </button>

    <!-- Next -->
    <button
      type="button"
      @click="next"
      class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/40 text-white px-3 py-2 rounded hover:bg-black/60 transition"
      aria-label="Seguinte"
    >
      ›
    </button>

    <!-- dots -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
      <button
        v-for="(slide, i) in slides"
        :key="'dot' + i"
        type="button"
        @click="goTo(i)"
        class="w-3 h-3 rounded-full transition"
        :class="index === i ? 'bg-white' : 'bg-white/50 hover:bg-white/70'"
        :aria-label="`Ir para slide ${i + 1}`"
      />
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      index: 0,
      slides: [
        { image: '/images/banner1.jpg' },
        { image: '/images/banner2.jpg' },
        { image: '/images/banner3.jpg' },
      ],
      timer: null,
    };
  },

  mounted() {
    this.startAutoplay();
  },

  beforeUnmount() {
    clearInterval(this.timer);
  },

  methods: {
    goTo(i) {
      this.index = i;
      this.scroll();
    },

    next() {
      this.index = (this.index + 1) % this.slides.length;
      this.scroll();
    },

    prev() {
      this.index = (this.index - 1 + this.slides.length) % this.slides.length;
      this.scroll();
    },

    scroll() {
      const el = this.$refs.carousel;
      if (!el || !el.children[this.index]) return;
      const slide = el.children[this.index];
      el.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
    },

    startAutoplay() {
      this.pause();
      this.timer = setInterval(() => {
        this.next();
      }, 5000);
    },

    pause() {
      clearInterval(this.timer);
      this.timer = null;
    },
  },
};
</script>
