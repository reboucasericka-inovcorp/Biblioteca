<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carrinho abandonado</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <p>Olá {{ $user->name }},</p>

    <p>Notámos que deixou artigos no carrinho na Inovcorp Library há mais de uma hora.</p>

    <p>Se ainda deseja concluir a compra, aceda ao carrinho e finalize o checkout:</p>

    <p>
        <a href="{{ url('/cart') }}" style="display: inline-block; padding: 10px 20px; background: #1e40af; color: white; text-decoration: none; border-radius: 6px;">Ver carrinho</a>
    </p>

    <p>Obrigado,<br>Inovcorp Library</p>
</body>
</html>
