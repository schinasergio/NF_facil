<!DOCTYPE html>
<html>

<head>
    <title>Inutilização de Numeração</title>
</head>

<body>
    <h1>Inutilizar Numeração de NFe</h1>

    @if($errors->any())
        <div style="color: red">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('nfe.inutilization.store') }}" method="POST">
        @csrf

        <div>
            <label>Série:</label>
            <input type="number" name="serie" value="1" required>
        </div>

        <div>
            <label>Número Inicial:</label>
            <input type="number" name="numero_inicial" required>
        </div>

        <div>
            <label>Número Final:</label>
            <input type="number" name="numero_final" required>
        </div>

        <div>
            <label>Justificativa (mín. 15 caracteres):</label>
            <textarea name="justificativa" required minlength="15"></textarea>
        </div>

        <button type="submit">Solicitar Inutilização</button>
    </form>

    <a href="{{ route('nfe.index') }}">Voltar</a>
</body>

</html>