@component('mail::message')
# Atualização do seu review

O estado do seu review foi atualizado.

**Livro:** {{ $review->book->name }}  
**Estado:** {{ $review->status }}  
**Rating:** {{ $review->rating }}/5  
**Comentário:** {{ $review->comment }}

@if($review->status === \App\Models\Review::STATUS_REFUSED && $review->refusal_reason)
**Justificativa da recusa:** {{ $review->refusal_reason }}
@endif

@component('mail::button', ['url' => url('/books/'.$review->book_id)])
Ver livro
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
