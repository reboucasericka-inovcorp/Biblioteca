@component('mail::message')
# Lembrete de devolução

A requisição **{{ $requisition->sequential_number }}** deve ser devolvida **amanhã**.

**Livro:** {{ $requisition->book?->name ?? '-' }}

**Data limite:** {{ $requisition->due_date?->format('d/m/Y') ?? '-' }}

Por favor, entregue o livro na biblioteca até à data indicada.

Obrigado,
{{ config('app.name') }}
@endcomponent
