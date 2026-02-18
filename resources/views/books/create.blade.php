<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Criar Livro
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <x-validation-errors class="mb-4" />

                    <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-label for="name" value="Nome" />
                            <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                        </div>

                        <div class="mt-4">
                            <x-label for="isbn" value="ISBN" />
                            <x-input id="isbn" class="block mt-1 w-full" type="text" name="isbn" :value="old('isbn')" required />
                        </div>

                        <div class="mt-4">
                            <x-label for="price" value="Preço (€)" />
                            <x-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price')" required />
                        </div>

                        <div class="mt-4">
                            <x-label for="publisher_id" value="Editora" />
                            <select id="publisher_id" name="publisher_id" class="block mt-1 w-full border-steel-gray rounded-md shadow-sm focus:ring-electric-blue focus:border-electric-blue" required>
                                <option value="">Selecione...</option>
                                @foreach($publishers as $p)
                                    <option value="{{ $p->id }}" {{ old('publisher_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-label for="authors" value="Autores" />
                            <select id="authors" name="authors[]" multiple class="block mt-1 w-full border-steel-gray rounded-md shadow-sm focus:ring-electric-blue focus:border-electric-blue min-h-[120px]">
                                @foreach($authors as $a)
                                    <option value="{{ $a->id }}" {{ in_array($a->id, old('authors', [])) ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-night-blue/70 mt-1">Segure Ctrl (ou Cmd) para selecionar vários autores.</p>
                        </div>

                        <div class="mt-4">
                            <x-label for="bibliography" value="Bibliografia" />
                            <textarea id="bibliography" name="bibliography" rows="4" class="block mt-1 w-full border-steel-gray rounded-md shadow-sm focus:ring-electric-blue focus:border-electric-blue">{{ old('bibliography') }}</textarea>
                        </div>

                        <div class="mt-4">
                            <x-label for="cover" value="Capa" />
                            <input id="cover" type="file" name="cover" accept="image/*" class="block mt-1 w-full" onchange="previewImage(this, 'cover-preview')">
                            <img id="cover-preview" class="mt-2 h-32 object-cover rounded hidden" src="" alt="Preview">
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-steel-gray rounded-md font-semibold text-xs text-night-blue uppercase tracking-widest shadow-sm hover:bg-steel-gray/20 focus:outline-none focus:ring-2 focus:ring-electric-blue focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-button type="submit">Criar</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
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
        }
    </script>
</x-app-layout>
