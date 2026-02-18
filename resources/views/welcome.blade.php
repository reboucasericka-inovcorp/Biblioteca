<x-public-layout>
    {{-- HERO --}}
    <section class="bg-night-blue text-white py-16">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h1 class="text-5xl font-bold text-white mb-6">
                Biblioteca Digital Inovcorp
            </h1>
            <p class="text-lg text-steel-gray max-w-2xl mx-auto mb-10">
                Explore o nosso acervo técnico e literário exclusivo para colaboradores.
            </p>           
        </div>
    </section>


    {{-- PUBLICAÇÕES (Vue - mount único) --}}
    <div id="app" data-auth="{{ auth()->check() ? '1' : '0' }}">
        <public-books-section></public-books-section>
    </div>


   

    {{-- FOOTER --}}
    <footer class="bg-night-blue text-steel-gray py-10">
        <div class="max-w-6xl mx-auto px-6">

            {{-- Linha superior --}}
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-6">

                {{-- Branding --}}
                <div class="text-center md:text-left">
                    <h3 class="text-xl font-semibold text-white">Biblioteca Digital Inovcorp</h3>
                    <p class="text-sm text-steel-gray/80 mt-1">
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
            <div class="border-t border-night-blue/50 mt-8 pt-6 text-center text-sm text-steel-gray/70">
                © {{ date('Y') }} Inovcorp — Biblioteca Digital. Todos os direitos reservados.
            </div>

        </div>
    </footer>   

</x-public-layout>
