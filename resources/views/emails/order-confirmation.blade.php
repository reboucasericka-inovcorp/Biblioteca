<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmação de compra</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <p>Olá {{ $order->customer_name }},</p>

    <p>Obrigado pela sua compra. A sua encomenda <strong>#{{ $order->id }}</strong> foi paga com sucesso.</p>

    <p><strong>Total:</strong> {{ number_format($order->total, 2, ',', ' ') }} €</p>

    <h4 style="margin-top: 1.5em;">Itens:</h4>
    <ul style="list-style: none; padding: 0;">
        @foreach($order->items as $item)
            <li style="margin-bottom: 0.5em;">
                {{ $item->book_title ?? $item->book?->name ?? 'Livro' }} — {{ $item->quantity }} × {{ number_format($item->unit_price, 2, ',', ' ') }} €
            </li>
        @endforeach
    </ul>

    <p style="margin-top: 1.5em;">
        <a href="{{ url('/') }}" style="display: inline-block; padding: 10px 20px; background: #1e40af; color: white; text-decoration: none; border-radius: 6px;">Voltar à livraria</a>
    </p>

    <p>Obrigado,<br>Inovcorp Library</p>
</body>
</html>
