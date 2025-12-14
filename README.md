# Sistema Emissor de NF-e (Laravel + Docker)

## 📌 Sobre o Projeto
Sistema emissor de Nota Fiscal Eletrônica (NF-e) desenvolvido em Laravel 11, utilizando Docker para containerização. O projeto segue uma arquitetura modular, com isolamento de tenant (Multitenancy por tabela unificada), API RESTful autenticada via Sanctum, e pipeline de CI/CD via GitHub Actions.

---

## 🚀 Tecnologias e Funcionalidades

### Backend
- **Framework**: Laravel 11 (PHP 8.2)
- **Banco de Dados**: MySQL 8.0
- **Autenticação**: Laravel Sanctum (API Tokens)
- **Documentação API**: Dedoc Scramble (Swagger/OpenAPI)
- **Fiscal**: NFePHP (Geração XML, Assinatura A1, Transmissão SEFAZ, DANFE/PDF, CC-e, Inutilização)

### Infraestrutura
- **Docker**: Ambientes separados para Desenvolvimento e Produção.
- **Nginx**: Servidor Web otimizado.
- **CI/CD**: GitHub Actions para testes automatizados.

### Frontend
- **Blade Templates**: Interface limpa e responsiva (Bootstrap 5).
- **Dashboard**: Gráficos analíticos com Chart.js.

---

## ⚙️ Instalação e Execução

### Pré-requisitos
- Docker Desktop instalado e rodando.
- Git.

### 1. Ambiente de Desenvolvimento (Local)
Use o script facilitador para Windows:
```powershell
./install_laravel.bat
```
Ou manualmente:
```bash
# Iniciar containers
docker compose -f infra/docker-compose.dev.yml up -d --build

# Instalar dependências
docker compose -f infra/docker-compose.dev.yml exec app composer install
docker compose -f infra/docker-compose.dev.yml exec app npm install && npm run build
docker compose -f infra/docker-compose.dev.yml exec app php artisan migrate --seed
```
Acesse:
- **Web App**: [http://localhost:8081](http://localhost:8081)
- **Documentação API**: [http://localhost:8081/docs/api](http://localhost:8081/docs/api)

### 2. Ambiente de Produção
Para simular ou rodar em produção:
```bash
# Build e Run com configurações de produção (Opcache, Sem DevDeps)
docker compose -f infra/docker-compose.prod.yml up -d --build
```

---

## 📚 Documentação da API
A documentação interativa (Swagger UI) é gerada automaticamente pelo **Scramble**.
Acesse `/docs/api` no seu navegador após iniciar o servidor.

### Endpoints Principais
- `POST /api/nfe`: Emitir uma nova NF-e.
  - Header: `Authorization: Bearer <seu-token>`
  - Header: `Accept: application/json`

---

## 🧪 Testes Automatizados
O projeto conta com uma suíte de testes robusta (Feature e Unit).
```bash
docker compose -f infra/docker-compose.dev.yml exec app php artisan test
```

### Principais Testes
- `ApiTest`: Verifica autenticação e emissão via API.
- `PolicyTest`: Verifica isolamento de dados entre usuários (Tenancy).
- `NFeTest`: Verifica geração de XML e Assinatura.
- `DanfeTest`: Verifica geração de PDF.

---

## 📦 Estrutura de Diretórios
- `app/Services/Fiscal`: Lógica pesada de NF-e (Integração SEFAZ, Certificados).
- `app/Policies`: Regras de autorização.
- `infra/`: Dockerfiles e Compose assets.
- `.github/workflows`: Pipelines de CI.

---

Desenvolvido por **Sérgio Schina** | SSA Soluções Tecnológicas
