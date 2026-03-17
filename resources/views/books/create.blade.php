<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Criar Livro
            </h2>
            <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                ← Voltar
            </a>
        </div>
    </x-slot>
    <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <x-validation-errors class="mb-4" />

                <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <x-label for="name" value="Nome" />
                            <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                        </div>

                        <div>
                            <x-label for="isbn" value="ISBN" />
                            <x-input id="isbn" class="block mt-1 w-full" type="text" name="isbn" :value="old('isbn')" required />
                        </div>

                        <div>
                            <x-label for="price" value="Preço (€)" />
                            <x-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price')" required />
                        </div>

                        <div>
                            <x-label for="discount" value="Desconto (%)" />
                            <x-input id="discount" class="block mt-1 w-full" type="number" step="0.01" min="0" max="100" name="discount" :value="old('discount', 0)" />
                        </div>

                        <div>
                            <x-label for="stock" value="Stock" />
                            <x-input id="stock" class="block mt-1 w-full" type="number" min="0" name="stock" :value="old('stock', 0)" />
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">Ativo (visível no catálogo)</span>
                            </label>
                        </div>

                        <div>
                            <x-label for="publisher_id" value="Editora" />
                            <select id="publisher_id" name="publisher_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Selecione...</option>
                                @foreach($publishers as $p)
                                    <option value="{{ $p->id }}" {{ old('publisher_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label for="authors" value="Autores" />
                            <select id="authors" name="authors[]" multiple class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 min-h-[120px]">
                                @foreach($authors as $a)
                                    <option value="{{ $a->id }}" {{ in_array($a->id, old('authors', [])) ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Segure Ctrl (ou Cmd) para selecionar vários autores.</p>
                        </div>

                        <div>
                            <x-label for="bibliography" value="Bibliografia" />
                            <textarea id="bibliography" name="bibliography" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('bibliography') }}</textarea>
                        </div>

                        <div>
                            <x-label for="cover" value="Capa" />
                            <input id="cover" type="file" name="cover" accept="image/*" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100" onchange="previewImage(this, 'cover-preview')">
                            <img id="cover-preview" class="mt-2 h-32 object-cover rounded hidden" src="" alt="Preview">
                        </div>

                        <div>
                            <x-label for="file" value="PDF do Livro (opcional)" />
                            <input id="file" type="file" name="file" accept="application/pdf" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                            <p class="text-sm text-gray-500 mt-1">Máx. 20 MB. Apenas ficheiros PDF.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                            Cancelar
                        </a>
                        <x-button type="submit">Criar</x-button>
                    </div>
                </form>
            </div>
        </div>
</x-admin-layout>
