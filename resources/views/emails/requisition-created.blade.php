@component('mail::message')

#  Nova Requisição

Olá **{{ $requisition->user->name }}**,

A sua requisição foi criada com sucesso.

---

**Número:** {{ $requisition->sequential_number }}  
**Livro:** {{ $requisition->book->name }}  
**Data da Requisição:** {{ $requisition->request_date->format('d/m/Y') }}  
**Data de Entrega:** {{ $requisition->due_date->format('d/m/Y') }}

@component('mail::button', ['url' => url('/requisitions')])
Ver Requisições
@endcomponent

Obrigado,<br>
{{ config('app.name') }}

@endcomponent
