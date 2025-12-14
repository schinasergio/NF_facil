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
            <li>
                {{ $company->razao_social }} ({{ $company->cnpj }})
                - <a href="{{ route('companies.certificate.create', $company) }}">
                    {{ $company->certificate ? 'Atualizar Certificado' : 'Upload Certificado' }}
                </a>
                @if($company->certificate)
                    <span style="color: green">[Válido até {{ $company->certificate->expires_at->format('d/m/Y') }}]</span>
                @endif
            </li>
        @endforeach
    </ul>
</body>

</html>