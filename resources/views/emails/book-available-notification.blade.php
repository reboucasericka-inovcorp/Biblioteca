@component('mail::message')
# Livro disponível novamente

O livro **{{ $book->name }}** voltou a estar disponível para requisição.

@component('mail::button', ['url' => url('/books/'.$book->id)])
Ver livro
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
