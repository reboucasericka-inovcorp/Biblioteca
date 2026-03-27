import './bootstrap';
import { showToast } from './toast';
import { createApp } from 'vue';
import Alpine from 'alpinejs';
import { createPinia } from 'pinia';
import BooksTable from './components/admin/BooksTable.vue';

window.showToast = showToast;
if (typeof window.chatUnreadCount !== 'number') {
  window.chatUnreadCount = 0;
}

/** Preview de imagem em inputs file (usado em formulários Blade; deve estar no window para não ter <script> dentro de #app). */
window.previewImage = function (input, previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      preview.src = e.target.result;
      preview.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.classList.add('hidden');
  }
};

/**
 * Scripts de páginas públicas que antes estavam inline nas Blade templates.
 * Mantidos aqui para evitar <script> dentro de #app (que é compilado pelo Vue).
 */
document.addEventListener('DOMContentLoaded', () => {
  /**
   * Banner da página inicial (welcome.blade).
   * Só corre se existir o carrossel de banners.
   */
  const carousel = document.getElementById('banner-carousel');
  if (carousel) {
    const slides = carousel.querySelectorAll('.carousel-item');
    const dots = document.querySelectorAll('.banner-dot');
    const btnPrev = document.getElementById('banner-prev');
    const btnNext = document.getElementById('banner-next');
    let index = 0;
    const autoplayMs = 5000;
    let autoplayTimer = null;

    const goToSlide = (i) => {
      if (!slides.length) return;
      index = (i + slides.length) % slides.length;
      const slide = slides[index];
      if (!slide) return;
      // Só scroll horizontal dentro do carousel — não move a página
      carousel.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
      dots.forEach((btn) => {
        btn.classList.remove('opacity-100');
        btn.classList.add('opacity-70');
      });
      if (dots[index]) {
        dots[index].classList.remove('opacity-70');
        dots[index].classList.add('opacity-100');
      }
    };

    const startAutoplay = () => {
      if (autoplayTimer) clearInterval(autoplayTimer);
      autoplayTimer = setInterval(() => {
        index = (index + 1) % slides.length;
        goToSlide(index);
      }, autoplayMs);
    };

    dots.forEach((btn, i) => {
      btn.addEventListener('click', () => {
        goToSlide(i);
        startAutoplay();
      });
    });

    if (btnPrev) {
      btnPrev.addEventListener('click', () => {
        goToSlide(index - 1);
        startAutoplay();
      });
    }

    if (btnNext) {
      btnNext.addEventListener('click', () => {
        goToSlide(index + 1);
        startAutoplay();
      });
    }

    goToSlide(0);
    startAutoplay();
  }

  /**
   * Página de sucesso do checkout.
   * Usa a rota /checkout/success — limpar o carrinho apenas aí.
   */
  if (window.location && window.location.pathname.startsWith('/checkout/success')) {
    try {
      localStorage.removeItem('library_cart');
      window.dispatchEvent(new Event('cart-updated'));
    } catch (e) {
      // falha silenciosa; não deve quebrar a página
    }
  }
});

const pinia = createPinia();
import AuthorsTable from './components/admin/AuthorsTable.vue';
import PublishersTable from './components/admin/PublishersTable.vue';
import BookDetail from './components/public/BookDetail.vue';
import RequisitionsTable from './components/admin/RequisitionsTable.vue';
import PublicBooksSection from './components/public/PublicBooksSection.vue';
import BooksGrid from './components/public/BooksGrid.vue';
import GoogleBooksSearch from './components/public/GoogleBooksSearch.vue';
import HeaderGoogleSearch from './components/public/HeaderGoogleSearch.vue';
import BannerCarousel from './components/public/BannerCarousel.vue';
import AccountMenu from './components/public/AccountMenu.vue';
import CartCount from './components/public/CartCount.vue';
import CategoriesMenu from './components/public/CategoriesMenu.vue';
import CartPage from './components/public/CartPage.vue';
import CheckoutPage from './components/public/CheckoutPage.vue';
import CitizenDashboard from './components/dashboard/CitizenDashboard.vue';
import AdminSuggestions from './components/admin/AdminSuggestions.vue';
import OrdersTable from './components/admin/OrdersTable.vue';
import SalesDashboard from './components/admin/SalesDashboard.vue';
import AdminStockTools from './components/admin/AdminStockTools.vue';
import UsersTable from './components/admin/UsersTable.vue';
import ReviewsTable from './components/admin/ReviewsTable.vue';
import ChatLayout from './components/chat/ChatLayout.vue';
import ChatWidget from './components/chat/ChatWidget.vue';

// Criar a app Vue e montar em #app; o HTML dentro de #app é usado como template,
// mas evitamos manipular innerHTML manualmente.
const appEl = document.getElementById('app');
const app = createApp({});
app.use(pinia);
app.component('books-table', BooksTable);
app.component('authors-table', AuthorsTable);
app.component('publishers-table', PublishersTable);
app.component('book-detail', BookDetail);
app.component('requisitions-table', RequisitionsTable);
app.component('public-books-section', PublicBooksSection);
app.component('books-grid', BooksGrid);
app.component('google-books-search', GoogleBooksSearch);
app.component('header-google-search', HeaderGoogleSearch);
app.component('banner-carousel', BannerCarousel);
app.component('account-menu', AccountMenu);
app.component('cart-count', CartCount);
app.component('categories-menu', CategoriesMenu);
app.component('cart-page', CartPage);
app.component('checkout-page', CheckoutPage);
app.component('citizen-dashboard', CitizenDashboard);
app.component('admin-suggestions', AdminSuggestions);
app.component('orders-table', OrdersTable);
app.component('sales-dashboard', SalesDashboard);
app.component('admin-stock-tools', AdminStockTools);
app.component('users-table', UsersTable);
app.component('reviews-table', ReviewsTable);
app.component('chat-layout', ChatLayout);
if (appEl) {
  app.mount('#app');
}

/* Alpine: só iniciar se Livewire não estiver presente (evita "multiple instances of Alpine") */
if (!window.Livewire) {
  window.Alpine = Alpine;
  Alpine.start();
}

const widgetEl = document.getElementById('chat-widget-root');
if (widgetEl) {
  const widgetApp = createApp({});
  widgetApp.use(createPinia());
  widgetApp.component('chat-widget', ChatWidget);
  widgetApp.mount('#chat-widget-root');
}
