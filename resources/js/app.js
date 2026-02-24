import './bootstrap';
import { createApp } from 'vue';
import BooksTable from './components/BooksTable.vue';
import AuthorsTable from './components/AuthorsTable.vue';
import PublishersTable from './components/PublishersTable.vue';
import BookDetail from './components/BookDetail.vue';
import RequisitionsTable from './components/RequisitionsTable.vue';
import PublicBooksTable from './components/PublicBooksTable.vue';
import PublicBooksSection from './components/PublicBooksSection.vue';
import BooksGrid from './components/BooksGrid.vue';
import GoogleBooksSearch from './components/GoogleBooksSearch.vue';
import CitizenDashboard from './components/CitizenDashboard.vue';
import AdminSuggestions from './components/AdminSuggestions.vue';

const app = createApp({});
app.component('books-table', BooksTable);
app.component('authors-table', AuthorsTable);
app.component('publishers-table', PublishersTable);
app.component('book-detail', BookDetail);
app.component('requisitions-table', RequisitionsTable);
app.component('public-books-table', PublicBooksTable);
app.component('public-books-section', PublicBooksSection);
app.component('books-grid', BooksGrid);
app.component('google-books-search', GoogleBooksSearch);
app.component('citizen-dashboard', CitizenDashboard);
app.component('admin-suggestions', AdminSuggestions);

const el = document.getElementById('app');
if (el) {
    app.mount('#app');
}

