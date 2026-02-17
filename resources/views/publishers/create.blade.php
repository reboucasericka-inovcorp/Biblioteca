<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Criar Editora
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <x-validation-errors class="mb-4" />

                    <form method="POST" action="{{ route('publishers.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-label for="name" value="Nome" />
                            <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                        </div>

                        <div class="mt-4">
                            <x-label for="notes" value="Notas" />
                            <textarea id="notes" name="notes" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                        </div>

                        <div class="mt-4">
                            <x-label for="logo" value="Logo" />
                            <input id="logo" type="file" name="logo" accept="image/*" class="block mt-1 w-full" onchange="previewImage(this, 'logo-preview')">
                            <img id="logo-preview" class="mt-2 h-32 object-contain rounded hidden" src="" alt="Preview">
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <a href="{{ route('publishers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
