<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Criar Utilizador
            </h2>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                ← Voltar
            </a>
        </div>
    </x-slot>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="space-y-4 max-w-xl">
                    <div>
                        <x-label for="name" value="Nome" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                    </div>

                    <div>
                        <x-label for="email" value="Email" />
                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                    </div>

                    <div>
                        <x-label for="password" value="Palavra-passe" />
                        <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    </div>

                    <div>
                        <x-label for="password_confirmation" value="Confirmar palavra-passe" />
                        <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    </div>

                    <div>
                        <x-label for="role" value="Perfil" />
                        <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500" required>
                            <option value="Cidadao" {{ old('role', 'Cidadao') === 'Cidadao' ? 'selected' : '' }}>Cidadão</option>
                            <option value="Admin" {{ old('role') === 'Admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                        Cancelar
                    </a>
                    <x-button type="submit">Criar utilizador</x-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
