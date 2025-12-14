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
                NFe #{{ $nfe->numero }} - Valor: R$ {{ $nfe->valor_total }} - Status: <strong>{{ $nfe->status }}</strong>
                @if($nfe->status === 'signed' || $nfe->status === 'rejected')
                    <form action="{{ route('nfe.transmit', $nfe) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit">Transmitir p/ SEFAZ</button>
                    </form>
                @endif
                [<a href="#">Download XML</a>]
                @if($nfe->mensagem_sefaz)
                    <br><small style="color: gray">SEFAZ: {{ $nfe->mensagem_sefaz }}</small>
                @endif
            </li>
        @endforeach
    </ul>
    <br>
    <a href="{{ url('/') }}">Voltar</a>
</body>

</html>