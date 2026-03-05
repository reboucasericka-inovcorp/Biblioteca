@component('mail::message')
# Novo review pendente de moderação

Foi criado um review com estado **suspended**.

**Cidadão:** {{ $review->user->name }} ({{ $review->user->email }})  
**Livro:** {{ $review->book->name }}  
**Rating:** {{ $review->rating }}/5  
**Comentário:** {{ $review->comment }}

@component('mail::button', ['url' => $reviewUrl])
Ver detalhe do review
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
