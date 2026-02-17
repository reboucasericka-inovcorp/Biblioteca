<x-public-layout>
    {{-- HERO --}}
    <section class="bg-slate-900 text-white py-16">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h1 class="text-5xl font-bold text-white mb-6">
                Biblioteca Digital Inovcorp
            </h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto mb-10">
                Explore o nosso acervo técnico e literário exclusivo para colaboradores.
            </p>           
        </div>
    </section>


    {{-- PUBLICAÇÕES RECENTES --}}
    <section class="py-16 bg-slate-50">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-2xl font-semibold text-slate-800 mb-8">
                Publicações Recentes
            </h2>

            <div id="app" data-auth="{{ auth()->check() ? '1' : '0' }}">
                <public-books-section type="recent"></public-books-section>
            </div>
        </div>
    </section>


    {{-- TECNOLOGIA --}}
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-2xl font-semibold text-slate-800 mb-8">
                Tecnologia & Desenvolvimento
            </h2>

            <div id="app-tech" data-auth="{{ auth()->check() ? '1' : '0' }}">
                <public-books-section type="tech"></public-books-section>
            </div>
        </div>
    </section>


   

    {{-- FOOTER --}}
    <footer class="bg-slate-900 text-slate-300 py-10">
        <div class="max-w-6xl mx-auto px-6">

            {{-- Linha superior --}}
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-6">

                {{-- Branding --}}
                <div class="text-center md:text-left">
                    <h3 class="text-xl font-semibold text-white">Biblioteca Digital Inovcorp</h3>
                    <p class="text-sm text-slate-400 mt-1">
                        Conhecimento ao alcance de todos os colaboradores.
                    </p>
                </div>

                {{-- Links rápidos --}}
                <div class="flex gap-10 text-sm">
                    <ul class="space-y-2">
                        <li><a href="/" class="hover:text-white transition">Início</a></li>
                        <li><a href="{{ route('books.index') }}" class="hover:text-white transition">Catálogo</a></li>
                        <li><a href="/about" class="hover:text-white transition">Sobre</a></li>
                    </ul>

                    <ul class="space-y-2">
                        <li><a href="/help" class="hover:text-white transition">Ajuda</a></li>
                        <li><a href="/contact" class="hover:text-white transition">Contacto</a></li>
                        <li><a href="/privacy" class="hover:text-white transition">Privacidade</a></li>
                    </ul>
                </div>
            </div>

            {{-- Linha inferior --}}
            <div class="border-t border-slate-700 mt-8 pt-6 text-center text-sm text-slate-500">
                © {{ date('Y') }} Inovcorp — Biblioteca Digital. Todos os direitos reservados.
            </div>

        </div>
    </footer>   

</x-public-layout>
