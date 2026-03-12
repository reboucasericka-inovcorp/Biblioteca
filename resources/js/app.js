import './bootstrap';
import { showToast } from './toast';
import { createApp } from 'vue';
import Alpine from 'alpinejs';
import { createPinia } from 'pinia';
import BooksTable from './components/admin/BooksTable.vue';

window.showToast = showToast;

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
import UsersTable from './components/admin/UsersTable.vue';
import ReviewsTable from './components/admin/ReviewsTable.vue';

// #app contém só {{ $slot }}. Usar esse HTML como template para Vue compilar os custom elements (books-table, etc.).
const appEl = document.getElementById('app');
const app = createApp({ template: appEl ? appEl.innerHTML : '<div></div>' });
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
app.component('users-table', UsersTable);
app.component('reviews-table', ReviewsTable);

if (document.getElementById('app')) {
  app.mount('#app');
}

/* Alpine: só iniciar se Livewire não estiver presente (evita "multiple instances of Alpine") */
if (!window.Livewire) {
  window.Alpine = Alpine;
  Alpine.start();
}

