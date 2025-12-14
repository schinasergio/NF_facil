# Arquitetura de Ambientes com Laravel + Docker  
_Sistema Emissor de NFe_

---

## 1. Objetivo

Este documento define a **arquitetura de ambientes** (Desenvolvimento, Homologação e Produção) para o Sistema Emissor de NFe usando:

- **Backend**: Laravel (PHP 8.2+)
- **Banco de dados**: MySQL 8.x
- **Infra**: Docker/ Docker Compose
- **Hospedagem**:
  - Dev: máquinas dos desenvolvedores
  - Homologação: VPS (ou Raspberry usada apenas como lab secundário)
  - Produção: VPS na nuvem (ex.: Azure) com Docker

---

## 2. Visão Geral dos Ambientes

### 2.1. Desenvolvimento (local)

- Roda na máquina do desenvolvedor.
- Levantado via `docker compose up`.
- Banco de dados isolado (`nfe_dev`).
- Arquivos `.env.development` com configurações locais.
- Foco: rapidez, testes individuais, novas features.

### 2.2. Homologação

- Roda em uma **VPS** com Docker.
- Branch base: `develop`.
- Banco `nfe_homolog`.
- Certificados de **homologação** configurados.
- Acesso restrito (VPN, IPs específicos ou autorização por senha forte).
- Foco: QA, testes da contabilidade, UAT com clientes piloto.

### 2.3. Produção

- VPS dedicada (recomendado mínimo 2 vCPU / 4GB RAM).
- Branch base: `main`.
- Banco `nfe_prod`.
- Certificados **de produção**.
- HTTPS obrigatório (Let’s Encrypt).
- Monitoramento básico (uptime + logs).

---

## 3. Estrutura de pastas do projeto (nível infra)

```text
nfe-emissor/
 ├─ src/                      # Código Laravel (app, bootstrap, etc.)
 ├─ public/                   # Raiz pública (index.php, assets)
 ├─ config/                   # Configurações Laravel
 ├─ database/                 # Migrações, seeds, factories
 ├─ storage/                  # Logs, cache, arquivos gerados
 ├─ resources/                # Views Blade, assets, lang
 ├─ docs/                     # Documentos .md (os que estamos criando)
 ├─ infra/
 │   ├─ docker/
 │   │   ├─ php-fpm/
 │   │   │   └─ Dockerfile
 │   │   ├─ nginx/
 │   │   │   ├─ Dockerfile
 │   │   │   └─ default.conf
 │   │   └─ mysql/
 │   │       └─ my.cnf (opcional)
 │   ├─ docker-compose.dev.yml
 │   ├─ docker-compose.homolog.yml
 │   └─ docker-compose.prod.yml
 ├─ .env.example
 ├─ README.md
 └─ DEV_ONBOARDING.md
```

> Observação: para simplificar, é possível usar **um único** `docker-compose.yml` e variar apenas o arquivo `.env`, mas separar por ambiente deixa a intenção mais clara.

---

## 4. Docker Compose – Desenvolvimento

`infra/docker-compose.dev.yml`:

```yaml
version: "3.9"

services:
  app:
    build:
      context: ..
      dockerfile: infra/docker/php-fpm/Dockerfile
    container_name: nfe_app_dev
    working_dir: /var/www/html
    volumes:
      - ../:/var/www/html
    env_file:
      - ../.env.development
    depends_on:
      - db

  web:
    build:
      context: ..
      dockerfile: infra/docker/nginx/Dockerfile
    container_name: nfe_web_dev
    ports:
      - "8080:80"
    depends_on:
      - app
    volumes:
      - ../:/var/www/html

  db:
    image: mysql:8.0
    container_name: nfe_db_dev
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: nfe_dev
      MYSQL_USER: nfe
      MYSQL_PASSWORD: nfe
    ports:
      - "3307:3306"
    volumes:
      - nfe_db_dev_data:/var/lib/mysql

volumes:
  nfe_db_dev_data:
```

### 4.1. Dockerfile PHP-FPM

`infra/docker/php-fpm/Dockerfile`:

```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
```

### 4.2. Dockerfile NGINX

`infra/docker/nginx/Dockerfile`:

```dockerfile
FROM nginx:1.27

COPY infra/docker/nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html
```

### 4.3. NGINX default.conf

`infra/docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    index index.php index.html;
    server_name localhost;
    root /var/www/html/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 5. Docker Compose – Homologação

`infra/docker-compose.homolog.yml` (muito semelhante ao dev, mudando nomes, volumes e `.env`):

```yaml
version: "3.9"

services:
  app:
    image: sua-imagem-registry/nfe-app:latest
    container_name: nfe_app_homolog
    env_file:
      - ../.env.homolog
    depends_on:
      - db

  web:
    image: sua-imagem-registry/nfe-nginx:latest
    container_name: nfe_web_homolog
    depends_on:
      - app
    ports:
      - "80:80"
    # Em homolog, o código pode vir da imagem, não precisa bind mount

  db:
    image: mysql:8.0
    container_name: nfe_db_homolog
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: nfe_homolog
      MYSQL_USER: nfe
      MYSQL_PASSWORD: nfe
    volumes:
      - nfe_db_homolog_data:/var/lib/mysql

volumes:
  nfe_db_homolog_data:
```

> Aqui a ideia é que, em homolog, você já trabalhe com **imagens buildadas pelo CI/CD**, não mais com bind de volume do código local.

---

## 6. Docker Compose – Produção

`infra/docker-compose.prod.yml` (parecido com homolog, mas com atenção a segurança):

```yaml
version: "3.9"

services:
  app:
    image: sua-imagem-registry/nfe-app:prod
    container_name: nfe_app_prod
    restart: always
    env_file:
      - ../.env.production
    depends_on:
      - db

  web:
    image: sua-imagem-registry/nfe-nginx:prod
    container_name: nfe_web_prod
    restart: always
    depends_on:
      - app
    ports:
      - "80:80"
    # Em produção, o HTTPS pode ser terminação por proxy reverso ou NGINX com certbot

  db:
    image: mysql:8.0
    container_name: nfe_db_prod
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: senha_forte
      MYSQL_DATABASE: nfe_prod
      MYSQL_USER: nfe
      MYSQL_PASSWORD: senha_forte
    volumes:
      - nfe_db_prod_data:/var/lib/mysql
    # Em produção, considere usar banco gerenciado em vez de container

volumes:
  nfe_db_prod_data:
```

> **Recomendação forte**: em produção, se possível, use **MySQL gerenciado** (Azure Database for MySQL) e retire o serviço `db` do Docker Compose. Isso aumenta robustez e backup.

---

## 7. Variáveis de ambiente (arquivos .env)

### 7.1. .env.example (base)

```bash
APP_NAME="NFe Emissor"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=nfe_dev
DB_USERNAME=nfe
DB_PASSWORD=nfe

# SEFAZ / NFe
NFE_ENV=homolog
NFE_CERT_PATH=/var/www/html/storage/certs/empresa_homolog.pfx
NFE_CERT_PASSWORD=senha
```

### 7.2. .env.development

- `APP_ENV=local`
- `APP_DEBUG=true`
- `DB_HOST=db`
- `DB_DATABASE=nfe_dev`
- `NFE_ENV=homolog`

### 7.3. .env.homolog

- `APP_ENV=homolog`
- `APP_DEBUG=false`
- `APP_URL=http://ip_ou_dominio_homolog`
- `DB_DATABASE=nfe_homolog`
- `NFE_ENV=homolog`

### 7.4. .env.production

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://nfe.suaempresa.com.br`
- `DB_HOST=host_mysql_gerenciado`
- `DB_DATABASE=nfe_prod`
- `DB_USERNAME=usuario_prod`
- `DB_PASSWORD=senha_muito_forte`
- `NFE_ENV=producao`

---

## 8. Deploy simplificado

### 8.1. Homolog

Na VPS de homolog:

```bash
cd /opt/nfe-emissor
git pull origin develop
docker compose -f infra/docker-compose.homolog.yml pull
docker compose -f infra/docker-compose.homolog.yml up -d --build
php artisan migrate --force
```

### 8.2. Produção

Na VPS de produção (via CI/CD ou manual):

```bash
cd /opt/nfe-emissor
git pull origin main
docker compose -f infra/docker-compose.prod.yml pull
docker compose -f infra/docker-compose.prod.yml up -d --build
php artisan migrate --force
```

---

## 9. Monitoramento básico

- Configurar **healthcheck** para app (ex.: `/health`).
- Usar um serviço tipo UptimeRobot para monitorar:
  - Homolog: `http://homolog.seudominio.com/health`
  - Prod: `https://nfe.seudominio.com/health`
- Coletar logs de:
  - `storage/logs/laravel.log`
  - Logs do container (Docker logs).

---

## 10. Próximos passos

- Integrar CI/CD (GitHub Actions, GitLab CI, Azure DevOps) para:
  - Buildar imagens `nfe-app` e `nfe-nginx`.
  - Publicar em registry.
  - Disparar deploy automatizado para homolog.
- Documentar em `DEV_ONBOARDING.md` o fluxo completo:
  - Como rodar dev local.
  - Como subir/derrubar containers.
  - Como rodar testes e migrações.
