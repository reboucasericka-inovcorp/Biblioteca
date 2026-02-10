import './bootstrap';
import { createApp } from 'vue';
import BooksTable from './components/BooksTable.vue';
import AuthorsTable from './components/AuthorsTable.vue';
import PublishersTable from './components/PublishersTable.vue';

const app = createApp({});
app.component('books-table', BooksTable);
app.component('authors-table', AuthorsTable);
app.component('publishers-table', PublishersTable);
app.mount('#app');

