<!DOCTYPE html>
<html>

<head>
    <title>Notas Fiscais</title>
</head>

<body>
    <h1>Notas Fiscais Emitidas</h1>
    <a href="{{ route('nfe.create') }}">Nova NFe</a>
    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif
    <ul>
        @foreach($nfes as $nfe)
            <li>
                NFe #{{ $nfe->numero }} - Valor: R$ {{ $nfe->valor_total }} - Status: {{ $nfe->status }}
                [<a href="#">Download XML</a>]
            </li>
        @endforeach
    </ul>
    <br>
    <a href="{{ url('/') }}">Voltar</a>
</body>

</html>