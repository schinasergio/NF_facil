# Guia de Desenvolvimento – Laravel  
_Sistema Emissor de NFe_

---

## 1. Objetivo

Este documento é um **guia prático para novos desenvolvedores** entrarem no projeto do Sistema Emissor de NFe, baseado em **Laravel + MySQL + Docker**.

Ao final, o dev deve ser capaz de:

- Subir o ambiente local com Docker.
- Entender a estrutura principal do projeto.
- Criar novas features seguindo o fluxo padrão (branch, PR, revisão).
- Rodar migrações, seeds, testes e comandos de fila.

---

## 2. Pré-requisitos para o desenvolvedor

### 2.1. Conhecimentos técnicos desejáveis

- PHP 8.x (orientação a objetos, namespaces, PSR)
- Laravel (rotas, controllers, models, migrations, queues)
- SQL / MySQL
- Noções de Docker e Docker Compose
- Git (branches, commits, pull requests)

### 2.2. Ferramentas recomendadas

- **Sistema operacional**: Linux ou Windows 10/11 (WSL2 ajuda bastante), macOS.
- **Editor/IDE**: VS Code (com extensões de PHP, Laravel, Docker) ou PHPStorm.
- **Git**: Git for Windows ou CLI.
- **Docker Desktop** (Windows/macOS) ou Docker Engine (Linux).

---

## 3. Primeiro acesso ao repositório

1. Solicite acesso ao repositório Git da empresa.
2. Clone o repositório:

```bash
git clone https://seu-repo.git nfe-emissor
cd nfe-emissor
```

3. Copie o arquivo `.env.example` para `.env.development`:

```bash
cp .env.example .env.development
```

4. Gere a chave da aplicação Laravel (depois que o container estiver no ar):

```bash
# Dentro do container app, ver seção 4
php artisan key:generate
```

---

## 4. Subindo o ambiente local com Docker

Dentro da pasta `infra/`:

```bash
cd infra
docker compose -f docker-compose.dev.yml up -d
```

Isso irá subir:

- `nfe_app_dev` – container com PHP-FPM + Laravel
- `nfe_web_dev` – container NGINX servindo `public/`
- `nfe_db_dev` – container MySQL 8

Acesse o sistema em:

```text
http://localhost:8080
```

### 4.1. Acessando o container app

Para executar comandos Artisan:

```bash
docker compose -f docker-compose.dev.yml exec app bash
# dentro do container:
php artisan key:generate
php artisan migrate
php artisan db:seed   # se existirem seeds
```

---

## 5. Estrutura básica do projeto Laravel

Estrutura de diretórios (principais):

```text
app/
 ├─ Console/                # Comandos Artisan customizados
 ├─ Exceptions/
 ├─ Http/
 │   ├─ Controllers/        # Controllers da aplicação
 │   ├─ Middleware/
 │   └─ Requests/           # FormRequests (validação)
 ├─ Models/                 # Models Eloquent
 ├─ Services/               # Serviços de negócios (NFe, Fiscal, etc.)
 ├─ Policies/               # Regras de autorização
 ├─ Providers/              # Providers Laravel
 └─ ...
bootstrap/
config/
database/
 ├─ migrations/             # Migrações do banco
 ├─ seeders/                # Seeders
 └─ factories/
resources/
 ├─ views/                  # Views Blade
 └─ lang/
routes/
 ├─ web.php                 # Rotas web
 ├─ api.php                 # Rotas API
 └─ console.php
storage/
tests/
 ├─ Feature/
 └─ Unit/
```

> Arquitetura detalhada de módulos (App\NFe, App\Fiscal, etc.) está no documento:  
> **`Design_Modulos_Laravel_Sistema_Emissor_NFe.md`**.

---

## 6. Fluxo de trabalho de desenvolvimento

### 6.1. Branches

- `main` – versão estável em produção.
- `develop` – branch de integração para homologação.
- `feature/nome-da-feature` – para cada tarefa ou conjunto de mudanças.

### 6.2. Criando uma feature

1. Atualize seu repositório:

```bash
git checkout develop
git pull origin develop
```

2. Crie a branch:

```bash
git checkout -b feature/nome-da-feature
```

3. Faça as alterações de código, migrations, testes etc.
4. Rode os testes localmente:

```bash
docker compose -f infra/docker-compose.dev.yml exec app php artisan test
```

5. Commit com mensagem clara:

```bash
git add .
git commit -m "Implementa cadastro de natureza de operação"
```

6. Suba a branch:

```bash
git push origin feature/nome-da-feature
```

7. Abra um **Pull Request** para `develop`, solicitando revisões.

---

## 7. Banco de dados – migrações e seeds

### 7.1. Criar uma nova migration

```bash
docker compose -f infra/docker-compose.dev.yml exec app \
  php artisan make:migration create_tb_natureza_operacao_table
```

Edite o arquivo gerado em `database/migrations`.

### 7.2. Rodar migrations

```bash
docker compose -f infra/docker-compose.dev.yml exec app php artisan migrate
```

### 7.3. Seeds

Crie seeds para dados iniciais (ex: regimes tributários, CFOP básicos):

```bash
docker compose -f infra/docker-compose.dev.yml exec app \
  php artisan make:seeder MatrizFiscalSeeder
```

Depois:

```bash
docker compose -f infra/docker-compose.dev.yml exec app php artisan db:seed
```

---

## 8. Padrões de código e boas práticas

### 8.1. PSR e estilo

- Seguir PSR-12.  
- Usar nomes de classes e métodos descritivos.
- Evitar “God classes”; separar responsabilidades em Services, Actions, etc.

### 8.2. Validação

- Usar **Form Requests** (`app/Http/Requests`) para validação de formulários.
- Nunca confiar apenas na validação do front-end.

### 8.3. Regras de negócio fiscais

- Centralizar o máximo possível em **Services** (`app/Services/Fiscal`, `app/Services/NFe`).
- Não encher controllers com lógica complexa.
- Regra fiscal que é reaproveitada deve ser bem comentada e **testada**.

### 8.4. Segurança

- Sempre usar **policies** e **gates** para controlar acesso a recursos.
- Nunca confiar apenas no front para controle de permissão.
- Cuidar para não vazar XMLs ou dados sensíveis em logs.

---

## 9. Logs e Debug

### 9.1. Logs Laravel

- Logs ficam em `storage/logs/laravel.log`.
- Em desenvolvimento, `APP_DEBUG=true` ajuda, mas **não usar isso em produção**.

### 9.2. Dump & Die (dd)

- `dd()` e `dump()` são úteis apenas em dev.
- Nunca deixar `dd()` em código commitado em PR final.

---

## 10. Testes

### 10.1. Testes Unitários

- Testar funções de cálculo de impostos, geração de XML, validações específicas.
- Colocar em `tests/Unit`.

### 10.2. Testes de Feature

- Testar rotas: cadastro de cliente, produto, emissão de NF-e, etc.
- Colocar em `tests/Feature`.

Executando:

```bash
docker compose -f infra/docker-compose.dev.yml exec app php artisan test
```

---

## 11. Comandos Artisan importantes

- `php artisan migrate` – aplica migrações.
- `php artisan db:seed` – executa seeds.
- `php artisan queue:work` – processa filas (em dev).
- `php artisan config:cache` – cache de config (homolog/prod).
- `php artisan route:list` – listar rotas.

---

## 12. Uso da IA (ChatGPT / Codex) no projeto

- IA pode ajudar com:
  - Geração de código boilerplate.
  - Sugestões de refatoração.
  - Explicação de trechos de código.

- Mas:
  - Todo código gerado deve ser **revisado**.
  - Regras fiscais devem ser **validadas com contabilidade**.
  - Não copy/paste cegamente sem entender.

---

## 13. Dúvidas e suporte interno

- Perguntas sobre regra fiscal: **contabilidade** ou **Product Manager**.
- Perguntas sobre arquitetura: **System Architect / Líder Técnico**.
- Perguntas sobre processos (branches, releases): **Team Leader / PMO**.

Este guia é vivo: à medida que a equipe cresce, novas seções podem ser adicionadas com exemplos de código, screenshots e fluxos detalhados.
