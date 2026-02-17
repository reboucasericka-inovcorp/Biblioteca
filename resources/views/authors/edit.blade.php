<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Autor
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <x-validation-errors class="mb-4" />

                    <form method="POST" action="{{ route('authors.update', $author) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-label for="name" value="Nome" />
                            <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $author->name)" required />
                        </div>

                        <div class="mt-4">
                            <x-label for="photo" value="Foto" />
                            @if($author->photo)
                                <p class="text-sm text-gray-500 mt-1">Foto atual:</p>
                                <img src="{{ asset('storage/' . $author->photo) }}" alt="Foto" class="mt-2 h-32 object-cover rounded">
                            @endif
                            <input id="photo" type="file" name="photo" accept="image/*" class="block mt-1 w-full" onchange="previewImage(this, 'photo-preview')">
                            <img id="photo-preview" class="mt-2 h-32 object-cover rounded hidden" src="" alt="Preview">
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <a href="{{ route('authors.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-button type="submit">Atualizar</x-button>
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
