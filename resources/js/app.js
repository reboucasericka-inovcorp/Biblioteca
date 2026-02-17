import './bootstrap';
import { createApp } from 'vue';
import BooksTable from './components/BooksTable.vue';
import AuthorsTable from './components/AuthorsTable.vue';
import PublishersTable from './components/PublishersTable.vue';
import BookDetail from './components/BookDetail.vue';
import RequisitionsTable from './components/RequisitionsTable.vue';
import PublicBooksTable from './components/PublicBooksTable.vue';

const app = createApp({});
app.component('books-table', BooksTable);
app.component('authors-table', AuthorsTable);
app.component('publishers-table', PublishersTable);
app.component('book-detail', BookDetail);
app.component('requisitions-table', RequisitionsTable);
app.component('public-books-table', PublicBooksTable);

const el = document.getElementById('app');
if (el) {
    app.mount('#app');
}

