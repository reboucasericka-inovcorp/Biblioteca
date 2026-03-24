{{-- Navegação de vitrine (sem decisão por role). Usado só em layouts/public. --}}
<div class="w-full bg-[#000020] text-white">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-3 grid grid-cols-1 lg:grid-cols-[220px_1fr_140px] items-center gap-4">
        <div class="relative group">
            <button
                type="button"
                class="flex items-center gap-2 font-semibold uppercase tracking-wide"
                aria-haspopup="true"
                aria-expanded="false">
                <span>☰</span>
                <span>Categorias</span>
            </button>
            <div
                class="absolute left-0 top-full mt-2 w-[760px] max-w-[95vw] bg-white text-gray-800 rounded-sm shadow-2xl border border-gray-200 z-50 hidden group-hover:block group-focus-within:block">
                <div class="px-5 py-4 border-b border-gray-100 text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                    ☰ Categorias
                </div>

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
            <a href="{{ url('/') }}" class="hover:underline">Destaques</a>
            <a href="{{ url('/') }}" class="hover:underline">Mais Vendidos</a>
            <a href="{{ url('/') }}" class="hover:underline">Autores</a>
            <a href="{{ url('/') }}" class="hover:underline">Editoras</a>
            <a href="{{ url('/') }}" class="hover:underline">Novidades</a>
        </div>

        <div class="text-right">
            <a href="#" class="font-medium uppercase text-sm hover:underline">Ofertas</a>
        </div>
    </div>
</div>
