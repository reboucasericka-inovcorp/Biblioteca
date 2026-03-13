<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmação de encomenda</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <p>Olá,</p>

    <p>A sua encomenda <strong>#{{ $order->id }}</strong> foi confirmada.</p>

    <p><strong>Total:</strong> {{ number_format($order->total, 2, ',', ' ') }} €</p>

    <h4 style="margin-top: 1.5em;">Livros:</h4>
    <ul style="list-style: none; padding: 0;">
        @foreach($order->items as $item)
            <li style="margin-bottom: 0.5em;">
                {{ $item->book_title }} — {{ $item->quantity }} un.
            </li>
        @endforeach
    </ul>

    <p style="margin-top: 1.5em;">Obrigado,<br>Inovcorp Library</p>
</body>
</html>
