{{-- Cópia fiel do header original de resources/views/layouts/public.blade.php (commit 8aa14994) --}}
<!-- TOPO -->
<div class="w-full bg-[#000020] text-white text-sm">
    <div class="max-w-[1700px] mx-auto px-6 py-2 grid grid-cols-3 items-center gap-4">
        <!-- vazio à esquerda -->
        <div></div>
        <!-- telefone centro -->
        <div class="flex items-center justify-center gap-4">
            <span>(+351) 912 349 054</span>
            <a href="#" class="btn btn-xs rounded-full border-0 bg-white/15 text-white hover:bg-white/25">Suporte</a>
        </div>

        <!-- redes sociais direita -->
        <div class="flex items-center justify-end gap-3 text-white">

            <!-- Instagram -->
            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="text-white hover:opacity-80" aria-label="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                </svg>
            </a>

            <!-- YouTube -->
            <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" class="text-white hover:opacity-80" aria-label="YouTube">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                </svg>
            </a>

        </div>
    </div>
</div>


<!-- HEADER PRINCIPAL -->
<div class="w-full bg-[#000020] text-white">
    <div class="max-w-[1700px] mx-auto px-6 py-4 grid grid-cols-1 lg:grid-cols-[260px_1fr_320px] gap-6 items-center">
        <!-- esquerda -->
        <!-- LOGO -->
        <a href="{{ url('/') }}" class="text-3xl font-bold leading-tight">
            Inovcorp Library
        </a>
        <!-- BUSCA GOOGLE (compacta no centro) -->
        <header-google-search></header-google-search>

       
        <!-- CONTA + CARRINHO -->
        <div class="flex items-center justify-end gap-4 text-sm font-medium">

            @auth
            @php($user = Auth::user())

            <div x-data="{ open: false }" class="relative">

                <button
                    type="button"
                    @click="open = !open"
                    
                    aria-label="Menu do utilizador"
                    class="flex items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#000020] focus:ring-indigo-500">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <img
                        class="h-9 w-9 rounded-full object-cover shrink-0"
                        src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->name }}">
                    @else
                    <div class="h-9 w-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold shrink-0">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    @endif
                </button>

                <div
                    x-cloak
                    x-show="open"
                    @click.outside="open = false"
                    @keydown.escape.window="open = false"
                    x-transition.origin.top.right
                    class="absolute right-0 mt-2 z-50 bg-white text-gray-900 rounded-lg shadow-xl ring-1 ring-black/5 min-w-[20rem] w-[22rem]">

                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center gap-3">

                            <div class="shrink-0">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img
                                    class="h-10 w-10 rounded-full object-cover"
                                    src="{{ $user->profile_photo_url }}"
                                    alt="{{ $user->name }}">
                                @endif
                            </div>

                            <div class="min-w-0 flex-1 break-words leading-tight">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $user->name }}
                                </div>

                                <div class="text-sm text-gray-500 break-all">
                                    {{ $user->email }}
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="py-1">

                        <a
                            href="{{ route('profile.show') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Perfil
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Sair
                            </button>
                        </form>

                    </div>

                </div>
            </div>

            @else

            <div class="text-right leading-tight">
                <a href="{{ route('login') }}" class="block hover:underline">Login</a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="block hover:underline">Criar Conta</a>
                @endif
            </div>

            @endauth

            <!-- CARRINHO -->

            <a href="{{ url('/cart') }}" class="flex items-center gap-2 hover:opacity-90">
                <span class="text-xl">🛒</span>
                <cart-count></cart-count>
            </a>

        </div>













    </div>
</div>

<!-- MENU: Categorias, destaques, ofertas -->
<div class="w-full bg-[#000020] text-white">
    <div class="max-w-[1700px] mx-auto px-6 py-3 grid grid-cols-1 lg:grid-cols-[220px_1fr_140px] items-center gap-4">
        <!-- Layout em grid: menu de categorias à esquerda, links no centro, ofertas à direita -->
        <!-- Menu dropdown de categorias -->
        <div class="relative group">
            <button
                type="button"
                class="flex items-center gap-2 font-semibold uppercase tracking-wide"
                aria-haspopup="true"
                aria-expanded="false">
                <span>☰</span>
                <span>Categorias</span>
            </button>
            <!-- Dropdown de categorias (aparece ao passar o mouse) -->
            <div
                class="absolute left-0 top-full mt-2 w-[760px] max-w-[95vw] bg-white text-gray-800 rounded-sm shadow-2xl border border-gray-200 z-50 hidden group-hover:block group-focus-within:block">
                <div class="px-5 py-4 border-b border-gray-100 text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                    ☰ Categorias
                </div>
                <!-- Lista de categorias em 3 colunas -->

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-5 text-[12px] leading-6">
                    <ul class="space-y-1 uppercase">
                        <li><a href="#" class="hover:text-primary">Programação</a></li>
                        <li><a href="#" class="hover:text-primary">Inteligência Artificial</a></li>
                        <li><a href="#" class="hover:text-primary">Desenvolvimento web</a></li>
                        <li><a href="#" class="hover:text-primary">Banco de dados</a></li>
                        <li><a href="#" class="hover:text-primary">Redes e Segurança</a></li>
                        <li><a href="#" class="hover:text-primary">DevOps</a></li>
                        <li><a href="#" class="hover:text-primary">Ciência de Dados</a></li>
                        <li><a href="#" class="hover:text-primary">Cloud Computing</a></li>
                        <li><a href="#" class="hover:text-primary">Livros em destaque</a></li>
                    </ul>

                    <ul class="space-y-1 uppercase">
                        <li><a href="#" class="hover:text-primary">Linguagens de Programação</a></li>
                        <li><a href="#" class="hover:text-primary">Frameworks</a></li>
                        <li><a href="#" class="hover:text-primary">Sistemas Operacionais</a></li>
                        <li><a href="#" class="hover:text-primary">Arquitetura de Software</a></li>
                        <li><a href="#" class="hover:text-primary">Machine Learning</a></li>
                        <li><a href="#" class="hover:text-primary">Big Data</a></li>
                        <li><a href="#" class="hover:text-primary">Blockchain</a></li>
                        <li><a href="#" class="hover:text-primary">Internet das Coisas</a></li>
                        <li><a href="#" class="hover:text-primary">Carreira em TI</a></li>
                    </ul>

                    <ul class="space-y-1 uppercase">
                        <li><a href="#" class="hover:text-primary">Certificações</a></li>
                        <li><a href="#" class="hover:text-primary">Ferramentas de Desenvolvimento</a></li>
                        <li><a href="#" class="hover:text-primary">Metodologias Ágeis</a></li>
                        <li><a href="#" class="hover:text-primary">Teste de Software</a></li>
                        <li><a href="#" class="hover:text-primary">UX/UI Design</a></li>
                        <li><a href="#" class="hover:text-primary">Infraestrutura</a></li>
                        <li><a href="#" class="hover:text-primary">Segurança da Informação</a></li>
                        <li><a href="#" class="hover:text-primary">Novidades em Tecnologia</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-8 font-medium uppercase text-sm">
            <a href="#" class="hover:underline">Destaques</a>
            <a href="#" class="hover:underline">Mais Vendidos</a>
            <a href="#" class="hover:underline">Autores</a>
            <a href="#" class="hover:underline">Editoras</a>
            <a href="#" class="hover:underline">Novidades</a>
        </div>

        <div class="text-right">
            <a href="#" class="font-medium uppercase text-sm hover:underline">Ofertas</a>
        </div>
    </div>
</div>