<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Criar Autor
            </h2>
            <a href="{{ route('authors.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                ← Voltar
            </a>
        </div>
    </x-slot>
    <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <x-validation-errors class="mb-4" />

                <form method="POST" action="{{ route('authors.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4 max-w-xl">
                        <div>
                            <x-label for="name" value="Nome" />
                            <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                        </div>

                        <div>
                            <x-label for="photo" value="Foto" />
                            <input id="photo" type="file" name="photo" accept="image/*" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100" onchange="previewImage(this, 'photo-preview')">
                            <img id="photo-preview" class="mt-2 h-32 object-cover rounded hidden" src="" alt="Preview">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('authors.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                            Cancelar
                        </a>
                        <x-button type="submit">Criar</x-button>
                    </div>
                </form>
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
</x-admin-layout>
