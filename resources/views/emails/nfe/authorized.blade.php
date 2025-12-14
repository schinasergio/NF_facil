<!DOCTYPE html>
<html>

<head>
    <title>Nota Fiscal Autorizada</title>
</head>

<body>
    <h1>Olá, {{ $customerName }}!</h1>
    <p>A Nota Fiscal emitida por <strong>{{ $companyName }}</strong> foi autorizada com sucesso.</p>
    <p>Detalhes:</p>
    <ul>
        <li>Número: {{ $nfe->numero }}</li>
        <li>Série: {{ $nfe->serie }}</li>
        <li>Valor Total: R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</li>
        <li>Chave de Acesso: {{ $nfe->chave }}</li>
    </ul>
    <p>Em anexo, você encontrará os arquivos XML e PDF (DANFE) da nota fiscal.</p>
    <p>Obrigado!</p>
</body>

</html>