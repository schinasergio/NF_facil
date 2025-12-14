<!DOCTYPE html>
<html>

<head>
    <title>Novo Destinatário</title>
</head>

<body>
    <h1>Novo Destinatário</h1>
    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <h3>Dados do Cliente</h3>
        <label>Razão Social/Nome: <input type="text" name="razao_social" value="{{ old('razao_social') }}"></label><br>
        <label>Fantasia: <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}"></label><br>
        <label>CPF/CNPJ: <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}"></label><br>
        <label>IE: <input type="text" name="ie" value="{{ old('ie') }}"></label><br>
        <label>Indicador IE (1, 2, 9): <input type="text" name="indicador_ie"
                value="{{ old('indicador_ie', '9') }}"></label><br>
        <label>Email: <input type="email" name="email" value="{{ old('email') }}"></label><br>
        <label>Telefone: <input type="text" name="telefone" value="{{ old('telefone') }}"></label><br>

        <h3>Endereço</h3>
        <label>Logradouro: <input type="text" name="logradouro" value="{{ old('logradouro') }}"></label><br>
        <label>Número: <input type="text" name="numero" value="{{ old('numero') }}"></label><br>
        <label>Complemento: <input type="text" name="complemento" value="{{ old('complemento') }}"></label><br>
        <label>Bairro: <input type="text" name="bairro" value="{{ old('bairro') }}"></label><br>
        <label>CEP: <input type="text" name="cep" value="{{ old('cep') }}"></label><br>
        <label>Cidade: <input type="text" name="cidade" value="{{ old('cidade') }}"></label><br>
        <label>UF: <input type="text" name="uf" value="{{ old('uf') }}"></label><br>
        <label>País: <input type="text" name="pais" value="Brasil"></label><br>

        <button type="submit">Salvar</button>
    </form>
    <br>
    <a href="{{ route('customers.index') }}">Voltar</a>
</body>

</html>