# Sistema Emissor de NF-e (Laravel + Docker)

## 📌 Sobre o Projeto
Sistema emissor de Nota Fiscal Eletrônica (NF-e) desenvolvido em Laravel 11, utilizando Docker para containerização. O projeto segue uma arquitetura modular para facilitar a manutenção e escalabilidade.

## 🚀 Tecnologias Utilizadas
- **PHP 8.2+**
- **Laravel 11**
- **MySQL 8.0**
- **Nginx**
- **Docker & Docker Compose**

## 🏗️ Estrutura do Projeto
- `app/Domain`: Regras de negócio e Entidades.
- `app/Services`: Lógica de aplicação e orquestração.
- `app/Integrations`: Comunicação com APIs externas (SEFAZ).
- `infra/`: Configurações de infraestrutura (Docker).

## ⚙️ Como Rodar o Projeto

### Pré-requisitos
- Docker Desktop instalado.

### Passo a Passo
1. **Clone o repositório**
   ```bash
   git clone https://github.com/schinasergio/NF_facil.git
   cd NF_facil
   ```

2. **Inicie o Ambiente**
   Execute o script de instalação automática (Windows):
   ```powershell
   ./install_laravel.bat
   ```
   Ou manualmente via Docker:
   ```bash
   docker compose -f infra/docker-compose.dev.yml up -d --build
   docker compose -f infra/docker-compose.dev.yml exec app composer install
   docker compose -f infra/docker-compose.dev.yml exec app php artisan migrate
   ```

3. **Acesse a Aplicação**
   - Web: [http://localhost:8081](http://localhost:8081)
   - Banco de Dados (Host): Porta 3307

## 📅 Roadmap e Status

- [x] **Configuração de Ambiente** (Docker, Nginx, PHP, MySQL)
- [x] **Módulo de Empresas (Emitentes)**
    - [x] Cadastro de Empresas
    - [x] Cadastro de Endereços
- [x] **Módulo de Clientes (Destinatários)**
    - [x] Cadastro de Clientes
    - [x] Vínculo com Endereços
- [x] **Módulo de Produtos**
    - [x] Cadastro de Produtos (Simples)
- [x] **Fiscal e NFe**
    - [x] Upload de Certificado A1 (.pfx)
    - [x] Geração de XML Assinado
    - [x] Envio para SEFAZ (Autorização)
    - [x] Geração de DANFE (PDF)

## 🧪 Testes
Para rodar os testes automatizados:
```bash
docker compose -f infra/docker-compose.dev.yml exec app php artisan test
```
