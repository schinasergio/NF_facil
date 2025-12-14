<!DOCTYPE html>
<html>

<head>
    <title>Produtos</title>
</head>

<body>
    <h1>Produtos</h1>
    <a href="{{ route('products.create') }}">Novo Produto</a>
    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif
    <ul>
        @foreach($products as $product)
            <li>
                {{ $product->nome }} (SKU: {{ $product->codigo_sku }}) - R$
                {{ number_format($product->preco_venda, 2, ',', '.') }}
                <br>
                NCM: {{ $product->ncm }} | Unidade: {{ $product->unidade }}
            </li>
        @endforeach
    </ul>
    <br>
    <a href="{{ url('/') }}">Voltar</a>
</body>

</html>