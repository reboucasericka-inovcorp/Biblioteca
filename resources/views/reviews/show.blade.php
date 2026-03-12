<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Detalhe do Review #{{ $review->id }}
            </h2>
            <a href="{{ route('reviews.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                ← Voltar
            </a>
        </div>
    </x-slot>
    <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1 text-gray-900">{{ $review->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cidadão</dt>
                        <dd class="mt-1 text-gray-900">{{ $review->user->name }} ({{ $review->user->email }})</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Livro</dt>
                        <dd class="mt-1 text-gray-900">{{ $review->book->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Requisição</dt>
                        <dd class="mt-1 text-gray-900">#{{ $review->requisition->sequential_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Rating</dt>
                        <dd class="mt-1 text-gray-900">{{ $review->rating }}/5</dd>
                    </div>
                </dl>
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <dt class="text-sm font-medium text-gray-500">Comentário</dt>
                    <dd class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $review->comment }}</dd>
                </div>
                @if($review->refusal_reason)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <dt class="text-sm font-medium text-gray-500">Justificativa da recusa</dt>
                        <dd class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $review->refusal_reason }}</dd>
                    </div>
                @endif
            </div>
        </div>
</x-admin-layout>
