<!DOCTYPE html>
<html>

<head>
    <title>Upload Certificado Digital</title>
</head>

<body>
    <h1>Upload Certificado Digital - {{ $company->razao_social }}</h1>

    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('companies.certificate.store', $company) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label>Arquivo PFX: <input type="file" name="pfx_file" accept=".pfx" required></label><br>
        <br>
        <label>Senha do Certificado: <input type="password" name="password" required></label><br>
        <br>
        <button type="submit">Enviar e Validar</button>
    </form>
    <br>
    <a href="{{ route('companies.index') }}">Voltar</a>
</body>

</html>