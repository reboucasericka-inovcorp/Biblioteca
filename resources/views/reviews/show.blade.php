<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Detalhe do Review #{{ $review->id }}
        </h2>
    </x-slot>

    <div class="py-3">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow border border-steel-gray/50 space-y-4">
                <p><strong>Estado:</strong> {{ $review->status }}</p>
                <p><strong>Cidadão:</strong> {{ $review->user->name }} ({{ $review->user->email }})</p>
                <p><strong>Livro:</strong> {{ $review->book->name }}</p>
                <p><strong>Requisição:</strong> #{{ $review->requisition->sequential_number }}</p>
                <p><strong>Rating:</strong> {{ $review->rating }}/5</p>
                <p><strong>Comentário:</strong><br>{{ $review->comment }}</p>
                @if($review->refusal_reason)
                    <p><strong>Justificativa da recusa:</strong><br>{{ $review->refusal_reason }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
