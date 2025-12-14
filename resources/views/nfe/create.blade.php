<!DOCTYPE html>
<html>

<head>
    <title>Gerar NFe</title>
</head>

<body>
    <h1>Emitir Nova Nota Fiscal</h1>

    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('nfe.store') }}" method="POST">
        @csrf
        <label>Emitente:
            <select name="company_id" required>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->razao_social }}</option>
                @endforeach
            </select>
        </label><br><br>

        <label>Destinatário:
            <select name="customer_id" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->razao_social }}</option>
                @endforeach
            </select>
        </label><br><br>

        <h3>Produtos</h3>
        <div>
            @foreach($products as $product)
                <label>
                    <input type="checkbox" name="items[][product_id]" value="{{ $product->id }}">
                    {{ $product->nome }} (R$ {{ $product->preco_venda }})
                </label><br>
            @endforeach
        </div>
        <br>
        <button type="submit">Gerar e Assinar XML</button>
    </form>
    <br>
    <a href="{{ url('/') }}">Voltar</a>
</body>

</html>