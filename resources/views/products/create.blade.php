<!DOCTYPE html>
<html>

<head>
    <title>Novo Produto</title>
</head>

<body>
    <h1>Novo Produto</h1>
    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('products.store') }}" method="POST">
        @csrf
        <label>Nome: <input type="text" name="nome" value="{{ old('nome') }}"></label><br>
        <label>SKU: <input type="text" name="codigo_sku" value="{{ old('codigo_sku') }}"></label><br>
        <label>NCM (8 dígitos): <input type="text" name="ncm" value="{{ old('ncm') }}"></label><br>
        <label>CEST: <input type="text" name="cest" value="{{ old('cest') }}"></label><br>
        <label>Unidade (UN, KG...): <input type="text" name="unidade" value="{{ old('unidade', 'UN') }}"></label><br>
        <label>Preço Venda: <input type="text" name="preco_venda" value="{{ old('preco_venda') }}"></label><br>
        <label>Origem (0=Nac, 1=Imp...): <input type="text" name="origem" value="{{ old('origem', '0') }}"></label><br>

        <button type="submit">Salvar</button>
    </form>
    <br>
    <a href="{{ route('products.index') }}">Voltar</a>
</body>

</html>