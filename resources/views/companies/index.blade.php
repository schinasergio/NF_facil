<!DOCTYPE html>
<html>

<head>
    <title>Empresas</title>
</head>

<body>
    <h1>Empresas</h1>
    <a href="{{ route('companies.create') }}">Nova Empresa</a>
    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif
    <ul>
        @foreach($companies as $company)
            <li>{{ $company->razao_social }} ({{ $company->cnpj }})</li>
        @endforeach
    </ul>
</body>

</html>