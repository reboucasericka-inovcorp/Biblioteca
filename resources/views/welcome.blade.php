<x-public-layout>
    <!--
        Layout principal da página pública.
        Herda estilos e scripts do layout padrão (public-layout).
    -->

    {{-- BANNER CAROUSEL: max 1700px, alinhado ao resto da página --}}
    <section class="w-full relative overflow-hidden">
        <div class="max-w-[1700px] mx-auto px-6 relative">
            <div id="banner-carousel" class="carousel w-full h-[420px] overflow-x-auto scroll-smooth snap-x snap-mandatory rounded-lg">
                <div id="slide1" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner1.jpg') }}" alt="Banner 1" class="w-full h-full object-cover">
                </div>
                <div id="slide2" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner2.jpg') }}" alt="Banner 2" class="w-full h-full object-cover">
                </div>
                <div id="slide3" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner3.jpg') }}" alt="Banner 3" class="w-full h-full object-cover">
                </div>
                <div id="slide4" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner4.jpg') }}" alt="Banner 4" class="w-full h-full object-cover">
                </div>
                <div id="slide5" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner5.jpg') }}" alt="Banner 5" class="w-full h-full object-cover">
                </div>
                <div id="slide6" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner6.jpg') }}" alt="Banner 6" class="w-full h-full object-cover">
                </div>
                <div id="slide7" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner7.jpg') }}" alt="Banner 7" class="w-full h-full object-cover">
                </div>
                <div id="slide8" class="carousel-item relative w-full flex-none snap-start">
                    <img src="{{ asset('images/banners/banner8.jpg') }}" alt="Banner 8" class="w-full h-full object-cover">
                </div>
            </div>

            {{-- Setas (navegação manual) --}}
            <button type="button" id="banner-prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-10 btn btn-circle btn-sm bg-white/90 hover:bg-white text-base-content border-0 shadow-md" aria-label="Banner anterior">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button type="button" id="banner-next" class="absolute right-4 top-1/2 -translate-y-1/2 z-10 btn btn-circle btn-sm bg-white/90 hover:bg-white text-base-content border-0 shadow-md" aria-label="Próximo banner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            {{-- Indicadores (bolinhas) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="0" aria-label="Ir para slide 1"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="1" aria-label="Ir para slide 2"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="2" aria-label="Ir para slide 3"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="3" aria-label="Ir para slide 4"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="4" aria-label="Ir para slide 5"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="5" aria-label="Ir para slide 6"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="6" aria-label="Ir para slide 7"></button>
                <button type="button" class="banner-dot w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 transition-opacity" data-slide="7" aria-label="Ir para slide 8"></button>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var carousel = document.getElementById("banner-carousel");
        if (!carousel) return;
        var slides = carousel.querySelectorAll(".carousel-item");
        var dots = document.querySelectorAll(".banner-dot");
        var btnPrev = document.getElementById("banner-prev");
        var btnNext = document.getElementById("banner-next");
        var index = 0;
        var autoplayMs = 5000;
        var autoplayTimer = null;

        function goToSlide(i) {
            index = (i + slides.length) % slides.length;
            var slide = slides[index];
            // Só scroll horizontal dentro do carousel — não move a página
            carousel.scrollTo({ left: slide.offsetLeft, behavior: "smooth" });
            dots.forEach(function (btn) { btn.classList.remove("opacity-100"); btn.classList.add("opacity-70"); });
            if (dots[index]) {
                dots[index].classList.remove("opacity-70");
                dots[index].classList.add("opacity-100");
            }
        }

        function startAutoplay() {
            if (autoplayTimer) clearInterval(autoplayTimer);
            autoplayTimer = setInterval(function () {
                index = (index + 1) % slides.length;
                goToSlide(index);
            }, autoplayMs);
        }

        dots.forEach(function (btn, i) {
            btn.addEventListener("click", function () {
                goToSlide(i);
                startAutoplay();
            });
        });
        if (btnPrev) btnPrev.addEventListener("click", function () { goToSlide(index - 1); startAutoplay(); });
        if (btnNext) btnNext.addEventListener("click", function () { goToSlide(index + 1); startAutoplay(); });

        goToSlide(0);
        startAutoplay();
    });
    </script>



  
    
    <!--
        PRINCIPAIS CATEGORIAS: Atualizadas para refletir o foco em programação/tecnologia.
        Mantive a estrutura, classes e quantidade de itens originais.
    -->
    {{-- PRINCIPAIS CATEGORIAS --}}
    <section class="bg-base-200/40 py-6">
        <div class="max-w-[1700px] mx-auto px-6">
            <h2 class="text-[#000020] text-2xl font-semibold mb-4 uppercase tracking-wide">Principais Categorias</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-9">
                <a href="@auth {{ route('profile') }} @else {{ route('login') }} @endauth" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Minha Conta</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Lançamentos</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Programação</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Inteligência Artificial</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Desenvolvimento Web</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Banco de Dados</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Redes e Segurança</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">DevOps</a>
                <a href="#" class="bg-[#000020] text-white text-xs text-center px-3 py-3 border border-white/20 hover:bg-[#1e40af]">Cloud Computing</a>
            </div>
        </div>
    </section>
    {{-- LIVROS EM DESTAQUE (6 livros da API type=featured) --}}
    <section class="bg-base-200 py-12">
        <div class="max-w-[1700px] mx-auto px-6">
            <h2 class="text-[#000020] text-2xl font-semibold mb-6 uppercase tracking-wide">Livros em Destaque</h2>
            <public-books-section mode="featured"></public-books-section>
        </div>
    </section>

    {{-- PUBLICACOES RECENTES (30 livros, 6 colunas, 5 linhas) --}}
    <section class="py-20 bg-base-200/40">
        <div class="max-w-[1700px] mx-auto px-6">
            <h2 class="text-[#000020] text-2xl font-semibold mb-6 uppercase tracking-wide">Publicações Recentes</h2>
            <public-books-section mode="recent"></public-books-section>
        </div>
    </section>

    {{-- Espaço visual entre secções --}}
    <div class="py-12" aria-hidden="true"></div>

    {{-- OS MAIS VENDIDOS (tecnologia, 6 livros) --}}
    <section class="py-20 bg-white">
        <div class="max-w-[1700px] mx-auto px-6">
            <h2 class="text-[#000020] text-2xl font-semibold mb-6 uppercase tracking-wide">Tecnologia & Desenvolvimento</h2>
            <public-books-section mode="tech"></public-books-section>
        </div>
    </section>

    


    <!--
        FOOTER: Mantive a estrutura original, apenas ajustei o texto para refletir o foco técnico.
    -->  

    {{-- FOOTER (mesmo padrão do header: #000020 + branco) --}}
    <footer class="bg-[#000020] text-white py-10">
        <div class="max-w-6xl mx-auto px-6">
            {{-- Linha superior --}}
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-6">

                {{-- Branding --}}
                <div class="text-center md:text-left">
                    <h3 class="text-xl font-semibold text-white">Biblioteca Digital Inovcorp</h3>
                    <p class="text-sm text-white/80 mt-1">
                        Conhecimento ao alcance de todos os colaboradores.
                    </p>
                </div>

                {{-- Links rápidos --}}
                <div class="flex gap-10 text-sm text-white">
                    <ul class="space-y-2">
                        <li><a href="/" class="hover:text-white transition">Início</a></li>
                        <li><a href="{{ route('books.index') }}" class="hover:text-white transition">Catálogo</a></li>
                    </ul>
                </div>
            </div>

            {{-- Linha inferior --}}
            <div class="border-t border-white/20 mt-8 pt-6 text-center text-sm text-white/70">
                © {{ date('Y') }} Inovcorp — Biblioteca Digital. Todos os direitos reservados.
            </div>

        </div>
    </footer>   

</x-public-layout>
