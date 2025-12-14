<!DOCTYPE html>
<html>

<head>
    <title>Destinatários</title>
</head>

<body>
    <h1>Destinatários (Clientes)</h1>
    <a href="{{ route('customers.create') }}">Novo Cliente</a>
    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif
    <ul>
        @foreach($customers as $customer)
            <li>{{ $customer->razao_social }} ({{ $customer->cpf_cnpj }})</li>
        @endforeach
    </ul>
    <br>
    <a href="{{ url('/') }}">Voltar</a>
</body>

</html>