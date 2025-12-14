# Manual de Modelagem de Dados (ERD) – Sistema Emissor de NFe

## 1. Objetivo do Documento

Este **Manual de Modelagem de Dados (ERD)** descreve a estrutura lógica do banco de dados do **Sistema Emissor de NFe**, a ser implementado em **MySQL** e consumido por uma aplicação **PHP**.  

O objetivo é:

- padronizar a modelagem de dados;
- servir como referência para desenvolvedores backend, frontend, DBAs e contadores envolvidos;
- reduzir inconsistências entre módulos do sistema (cadastros, emissão, relatórios e integrações SEFAZ).

---

## 2. Visão Geral da Arquitetura de Dados

O banco de dados segue uma arquitetura relacional normalizada (até 3FN quando possível), com foco em:

1. **Integridade fiscal** – garantir que o que vai para o XML da NFe/NFCe/NFSe seja consistente.  
2. **Rastreabilidade** – permitir auditoria, logs de alterações e histórico de documentos.  
3. **Performance** – índices nas colunas de pesquisa frequente (chaves, datas, CNPJ/CPF, números de nota, etc.).

### 2.1 Módulos Lógicos

- **Cadastros básicos**  
  - Empresas (emitentes)  
  - Usuários e permissões  
  - Clientes / Destinatários  
  - Produtos / Serviços  
  - Tabelas fiscais (ICMS, PIS, COFINS, IPI etc.)  
  - Natureza de operação / CFOP / CST/CSOSN  

- **Documentos fiscais**  
  - NFe (modelo 55)  
  - NFCe (modelo 65)  
  - NFSe (serviços)  
  - CTe-e (opcional / futuro)  

- **Transações e Estoque**  
  - Itens de NFe  
  - Movimentações de estoque  
  - Financeiro (duplicatas / recebíveis simplificados)  

- **Infraestrutura**  
  - Certificados digitais  
  - Configurações SEFAZ (ambiente, UF, URLs, CSC, etc.)  
  - Logs de utilização e auditoria  

---

## 3. Convenções de Modelagem

- **Padrão de nomes de tabelas:** `tb_<nome>` em minúsculo. Exemplo: `tb_empresa`, `tb_nfe`.  
- **Padrão de nomes de colunas:** snake_case, em português, mantendo clareza fiscal.  
- **Chave primária:** `id_<tabela>` com tipo `BIGINT UNSIGNED AUTO_INCREMENT`.  
- **Chaves estrangeiras:** `id_<tabela_referenciada>`.  
- **Datas:** `DATETIME` para registros com hora; `DATE` onde hora não é relevante.  
- **Campos monetários:** `DECIMAL(15,2)` (até trilhões com 2 casas decimais).  
- **Campos de quantidade:** `DECIMAL(15,4)` (mais casas decimais para unidades fracionadas).  
- **Campos de status / enumerações:** `TINYINT` ou `ENUM` documentados neste manual.  

---

## 4. Entidades Principais e Relacionamentos

### 4.1 Empresa – `tb_empresa`

Representa um CNPJ emitente cadastrado no sistema.

**Campos principais**

- `id_empresa` (PK)  
- `cnpj` – `CHAR(14)`  
- `razao_social` – `VARCHAR(150)`  
- `nome_fantasia` – `VARCHAR(150)`  
- `inscricao_estadual` – `VARCHAR(20)`  
- `inscricao_municipal` – `VARCHAR(20)`  
- `regime_tributario` – `TINYINT`  
  - 1 = Simples Nacional  
  - 2 = Simples Nacional – excesso sublimite  
  - 3 = Regime Normal (Lucro Presumido/Real)  
- `crt_detalhe` – `VARCHAR(10)` (ex.: CSOSN / enquadramento adicional)  
- Endereço completo: `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `cep`, `codigo_municipio_ibge`  
- `telefone` – `VARCHAR(20)`  
- `email` – `VARCHAR(150)`  
- `ativo` – `TINYINT(1)`  
- `data_cadastro` – `DATETIME`  

**Relacionamentos**

- 1:N com **Usuários** (`tb_usuario`) – usuários vinculados à empresa.  
- 1:N com **Certificados Digitais** (`tb_certificado`) – controle de certificados A1.  
- 1:N com **NFe/NFCe/NFSe** – empresa como **emitente**.  

---

### 4.2 Usuário – `tb_usuario`

Controle de acesso ao sistema.

**Campos principais**

- `id_usuario` (PK)  
- `id_empresa` (FK -> `tb_empresa`)  
- `nome` – `VARCHAR(100)`  
- `email` – `VARCHAR(150)`  
- `senha_hash` – `VARCHAR(255)`  
- `perfil` – `ENUM('ADMIN','OPERACIONAL','CONTABILIDADE','VISUALIZADOR')`  
- `ativo` – `TINYINT(1)`  
- `ultimo_login` – `DATETIME`  

**Relacionamentos**

- N:1 com **Empresa**  
- 1:N com **Log de Utilização** (`tb_log_uso`)  
- 1:N com **NFe** e demais documentos (usuário que emitiu/cancelou).  

---

### 4.3 Cliente / Destinatário – `tb_cliente`

Cadastro de clientes/fornecedores/destinatários.

**Campos principais**

- `id_cliente` (PK)  
- `id_empresa` (FK) – empresa dona do cadastro  
- `tipo_pessoa` – `ENUM('F','J','E')` (Física, Jurídica, Exterior)  
- `cpf_cnpj` – `VARCHAR(14)`  
- `ie_rg` – `VARCHAR(20)`  
- `isento_ie` – `TINYINT(1)`  
- `razao_social_nome` – `VARCHAR(150)`  
- `nome_fantasia` – `VARCHAR(150)`  
- Endereço completo (semelhante à empresa)  
- `email_nfe` – `VARCHAR(150)`  
- `telefone` – `VARCHAR(20)`  
- `tipo_cadastro` – `ENUM('CLIENTE','FORNECEDOR','AMBOS')`  

**Relacionamentos**

- 1:N com **NFe** (`tb_nfe`) – destinatário.  
- 1:N com **NFSe** (`tb_nfse`) – tomador de serviços.  

---

### 4.4 Produto / Serviço – `tb_produto`

Tabela central para produtos/serviços vendidos.

**Campos principais**

- `id_produto` (PK)  
- `id_empresa` (FK)  
- `tipo_item` – `ENUM('PRODUTO','SERVICO')`  
- `codigo_interno` – `VARCHAR(60)`  
- `codigo_barras` – `VARCHAR(60)`  
- `descricao` – `VARCHAR(255)`  
- `ncm` – `VARCHAR(8)`  
- `cest` – `VARCHAR(7)` (opcional)  
- `cfop_padrao_entrada` – `VARCHAR(4)`  
- `cfop_padrao_saida` – `VARCHAR(4)`  
- `unidade` – `VARCHAR(6)`  
- `origem_mercadoria` – `TINYINT` (0 a 8, conforme tabela ICMS)  
- `preco_venda` – `DECIMAL(15,2)`  
- `preco_custo` – `DECIMAL(15,2)`  
- `estoque_atual` – `DECIMAL(15,4)`  
- `estoque_minimo` – `DECIMAL(15,4)`  
- `id_matriz_fiscal_icms` (FK -> `tb_matriz_fiscal`)  
- `id_matriz_fiscal_pis` (FK)  
- `id_matriz_fiscal_cofins` (FK)  
- `id_matriz_fiscal_ipi` (FK)  

**Relacionamentos**

- 1:N com **Itens de NFe** (`tb_nfe_item`)  
- 1:N com **Movimentações de Estoque** (`tb_estoque_mov`)  

---

### 4.5 Matriz Fiscal – `tb_matriz_fiscal`

Configuração fiscal reutilizável para tipos de itens x regime tributário.

**Campos principais**

- `id_matriz_fiscal` (PK)  
- `id_empresa` (FK)  
- `tipo_imposto` – `ENUM('ICMS','PIS','COFINS','IPI','ISS')`  
- `regime_fiscal` – compatível com `tb_empresa.regime_tributario`  
- `descricao` – `VARCHAR(255)`  
- `cst_csosn` – `VARCHAR(4)`  
- `aliquota` – `DECIMAL(5,2)`  
- `aliquota_st` – `DECIMAL(5,2)` (se aplicável)  
- `base_calculo_modalidade` – `TINYINT`  
- `vigencia_de` – `DATE`  
- `vigencia_ate` – `DATE`  

**Relacionamentos**

- 1:N com **Produto**  
- 1:N com **Natureza de Operação** (para aplicar impostos por operação)  

---

### 4.6 Natureza de Operação – `tb_natureza_operacao`

Tabela responsável por agrupar CFOP e parâmetros de operação.

**Campos principais**

- `id_natureza_operacao` (PK)  
- `id_empresa` (FK)  
- `codigo` – `VARCHAR(10)` (ex.: “V1”, “C1”, etc.)  
- `descricao` – `VARCHAR(255)`  
- `tipo_operacao` – `ENUM('ENTRADA','SAIDA')`  
- `cfop` – `VARCHAR(4)`  
- `gera_estoque` – `TINYINT(1)`  
- `gera_financeiro` – `TINYINT(1)`  
- `permite_nfce` – `TINYINT(1)`  
- `permite_nfe` – `TINYINT(1)`  
- `permite_nfse` – `TINYINT(1)`  

**Relacionamentos**

- 1:N com **NFe** (campo `id_natureza_operacao`)  
- 1:N com **NFSe** (natureza de serviço)  

---

### 4.7 Documento NFe – `tb_nfe`

Cabeçalho da Nota Fiscal Eletrônica modelo 55 (e NFCe modelo 65 com pequenas diferenças).

**Campos principais**

- `id_nfe` (PK)  
- `id_empresa` (FK)  
- `id_cliente` (FK)  
- `id_usuario_emissao` (FK -> `tb_usuario`)  
- `modelo` – `CHAR(2)` (55, 65, etc.)  
- `serie` – `INT`  
- `numero` – `INT`  
- `data_emissao` – `DATETIME`  
- `data_saida_entrada` – `DATETIME`  
- `tipo_operacao` – `TINYINT` (0=entrada, 1=saída)  
- `id_natureza_operacao` (FK)  
- `finalidade_emissao` – `TINYINT` (1=normal, 2=complementar, 3=ajuste, 4=devolução)  
- `forma_pagamento` – `TINYINT` (0=à vista, 1=a prazo, 2=outros)  
- `valor_produtos` – `DECIMAL(15,2)`  
- `valor_frete` – `DECIMAL(15,2)`  
- `valor_seguro` – `DECIMAL(15,2)`  
- `valor_desconto` – `DECIMAL(15,2)`  
- `valor_outros` – `DECIMAL(15,2)`  
- `valor_total_nfe` – `DECIMAL(15,2)`  
- `chave_acesso` – `CHAR(44)`  
- `protocolo_autorizacao` – `VARCHAR(50)`  
- `data_autorizacao` – `DATETIME`  
- `xml_assinado` – `LONGTEXT` (pode ser armazenado em outra tabela ou storage externo)  
- `status_nfe` – `TINYINT`  
  - 0 = rascunho  
  - 1 = assinada  
  - 2 = autorizada  
  - 3 = cancelada  
  - 4 = inutilizada  
- `motivo_cancelamento` – `VARCHAR(255)`  
- `data_cancelamento` – `DATETIME`  

**Relacionamentos**

- 1:N com **Itens da NFe** (`tb_nfe_item`)  
- 1:N com **Pagamentos** (`tb_nfe_pagamento`)  
- 1:1 com **Transporte** (`tb_nfe_transporte`)  
- 1:N com **Eventos NFe** (`tb_nfe_evento`)  

---

### 4.8 Itens da NFe – `tb_nfe_item`

Itens vinculados ao cabeçalho da nota.

**Campos principais**

- `id_nfe_item` (PK)  
- `id_nfe` (FK)  
- `id_produto` (FK)  
- `numero_item` – `INT`  
- `descricao` – `VARCHAR(255)`  
- `ncm` – `VARCHAR(8)`  
- `cfop` – `VARCHAR(4)`  
- `unidade` – `VARCHAR(6)`  
- `quantidade` – `DECIMAL(15,4)`  
- `valor_unitario` – `DECIMAL(15,4)`  
- `valor_total` – `DECIMAL(15,2)`  
- **Campos de impostos** (resumo por item):  
  - `icms_cst_csosn`, `icms_aliquota`, `icms_valor`  
  - `pis_cst`, `pis_aliquota`, `pis_valor`  
  - `cofins_cst`, `cofins_aliquota`, `cofins_valor`  
  - `ipi_cst`, `ipi_aliquota`, `ipi_valor`  

**Relacionamentos**

- N:1 com **NFe**  
- N:1 com **Produto**  
- Gatilho de movimentação de estoque na autorização/cancelamento.  

---

### 4.9 Pagamentos – `tb_nfe_pagamento`

Condição de pagamento da nota, compatível com o grupo `YA` da NFe.

**Campos principais**

- `id_nfe_pagamento` (PK)  
- `id_nfe` (FK)  
- `t_pag` – `VARCHAR(2)` (01=Dinheiro, 03=Cartão de Crédito, 15=PIX etc.)  
- `v_pag` – `DECIMAL(15,2)`  
- `ind_pag` – `TINYINT` (0=à vista, 1=a prazo, 2=outros)  
- `cnpj_credenciadora` – `CHAR(14)` (para cartões)  
- `bandeira` – `VARCHAR(2)`  
- `numero_autorizacao` – `VARCHAR(20)`  

---

### 4.10 Transporte – `tb_nfe_transporte`

Dados de frete associados à NFe.

**Campos principais**

- `id_nfe_transporte` (PK)  
- `id_nfe` (FK)  
- `mod_frete` – `TINYINT` (0=por conta do emitente, 1=por conta do destinatário etc.)  
- `cnpj_cpf_transportador` – `VARCHAR(14)`  
- `razao_social_transportador` – `VARCHAR(150)`  
- `placa_veiculo` – `VARCHAR(8)`  
- `uf_veiculo` – `CHAR(2)`  
- `valor_frete` – `DECIMAL(15,2)`  

---

### 4.11 Movimentações de Estoque – `tb_estoque_mov`

Histórico de movimentação gerado na autorização/cancelamento de documentos.

**Campos principais**

- `id_estoque_mov` (PK)  
- `id_empresa` (FK)  
- `id_produto` (FK)  
- `id_nfe_item` (FK, opcional)  
- `tipo_movimento` – `ENUM('ENTRADA','SAIDA','AJUSTE')`  
- `quantidade` – `DECIMAL(15,4)`  
- `data_movimento` – `DATETIME`  
- `origem` – `VARCHAR(50)` (NFE, NFSE, AJUSTE_MANUAL …)  
- `observacao` – `VARCHAR(255)`  

---

### 4.12 Logs de Utilização – `tb_log_uso`

Rastreia ações importantes de usuários.

**Campos principais**

- `id_log_uso` (PK)  
- `id_empresa` (FK)  
- `id_usuario` (FK)  
- `acao` – `VARCHAR(100)` (LOGIN, EMITIR_NFE, CANCELAR_NFE, ALTERAR_PRODUTO etc.)  
- `descricao` – `TEXT`  
- `data_evento` – `DATETIME`  
- `ip` – `VARCHAR(45)`  
- `user_agent` – `VARCHAR(255)`  

---

### 4.13 Certificados Digitais – `tb_certificado`

Gerencia certificados A1 armazenados no servidor.

**Campos principais**

- `id_certificado` (PK)  
- `id_empresa` (FK)  
- `tipo` – `ENUM('A1_PFX')`  
- `arquivo_pfx_path` – `VARCHAR(255)` (caminho seguro no servidor)  
- `senha_criptografada` – `VARCHAR(255)`  
- `data_validade` – `DATE`  
- `ativo` – `TINYINT(1)`  

---

### 4.14 NFSe – `tb_nfse` (Resumo)

Modelagem semelhante à NFe, porém focada em ISS e lista de serviços.

Campos principais incluem:

- `id_nfse`, `id_empresa`, `id_cliente` (tomador), `numero_rps`, `serie_rps`, `codigo_verificacao`, `valor_servicos`, `valor_iss`, `aliquota_iss`, `codigo_servico_municipal`, `codigo_cnae`, `xml_assinado`, `status_nfse` etc.  

Itens da NFSe (serviços) ficam em `tb_nfse_item`, semelhantes à `tb_nfe_item`, porém sem ICMS/IPI.

---

## 5. Relacionamentos Globais (Resumo)

De forma simplificada:

- **Empresa**  
  - 1:N Usuários  
  - 1:N Clientes  
  - 1:N Produtos  
  - 1:N NFe / NFCe / NFSe  
  - 1:N Matriz Fiscal  
  - 1:N Natureza de Operação  
  - 1:N Certificados  
  - 1:N Logs de Uso  

- **NFe**  
  - N:1 Empresa  
  - N:1 Cliente  
  - 1:N Itens  
  - 1:N Pagamentos  
  - 1:1 Transporte  
  - 1:N Eventos  
  - 1:N Movimentações de Estoque (indireto via itens)  

---

## 6. Exemplo de DDL (MySQL)

> **Observação:** O código abaixo é um _starter kit_ para criação das tabelas. Ajustes finos (engine, charset, collation, índices adicionais) podem ser feitos pelo DBA.

```sql
CREATE TABLE tb_empresa (
  id_empresa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cnpj CHAR(14) NOT NULL,
  razao_social VARCHAR(150) NOT NULL,
  nome_fantasia VARCHAR(150),
  inscricao_estadual VARCHAR(20),
  inscricao_municipal VARCHAR(20),
  regime_tributario TINYINT NOT NULL,
  crt_detalhe VARCHAR(10),
  logradouro VARCHAR(150),
  numero VARCHAR(10),
  complemento VARCHAR(50),
  bairro VARCHAR(80),
  cidade VARCHAR(80),
  uf CHAR(2),
  cep CHAR(8),
  codigo_municipio_ibge INT,
  telefone VARCHAR(20),
  email VARCHAR(150),
  ativo TINYINT(1) DEFAULT 1,
  data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_empresa_cnpj (cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

```sql
CREATE TABLE tb_usuario (
  id_usuario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_empresa BIGINT UNSIGNED NOT NULL,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  perfil ENUM('ADMIN','OPERACIONAL','CONTABILIDADE','VISUALIZADOR') NOT NULL,
  ativo TINYINT(1) DEFAULT 1,
  ultimo_login DATETIME,
  FOREIGN KEY (id_empresa) REFERENCES tb_empresa(id_empresa)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uk_usuario_email_empresa (id_empresa, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

```sql
CREATE TABLE tb_cliente (
  id_cliente BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_empresa BIGINT UNSIGNED NOT NULL,
  tipo_pessoa ENUM('F','J','E') NOT NULL,
  cpf_cnpj VARCHAR(14) NOT NULL,
  ie_rg VARCHAR(20),
  isento_ie TINYINT(1) DEFAULT 0,
  razao_social_nome VARCHAR(150) NOT NULL,
  nome_fantasia VARCHAR(150),
  logradouro VARCHAR(150),
  numero VARCHAR(10),
  complemento VARCHAR(50),
  bairro VARCHAR(80),
  cidade VARCHAR(80),
  uf CHAR(2),
  cep CHAR(8),
  codigo_municipio_ibge INT,
  email_nfe VARCHAR(150),
  telefone VARCHAR(20),
  tipo_cadastro ENUM('CLIENTE','FORNECEDOR','AMBOS') DEFAULT 'CLIENTE',
  FOREIGN KEY (id_empresa) REFERENCES tb_empresa(id_empresa)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_cliente_documento (cpf_cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

```sql
CREATE TABLE tb_produto (
  id_produto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_empresa BIGINT UNSIGNED NOT NULL,
  tipo_item ENUM('PRODUTO','SERVICO') NOT NULL,
  codigo_interno VARCHAR(60) NOT NULL,
  codigo_barras VARCHAR(60),
  descricao VARCHAR(255) NOT NULL,
  ncm VARCHAR(8),
  cest VARCHAR(7),
  cfop_padrao_entrada VARCHAR(4),
  cfop_padrao_saida VARCHAR(4),
  unidade VARCHAR(6) NOT NULL,
  origem_mercadoria TINYINT,
  preco_venda DECIMAL(15,2) DEFAULT 0,
  preco_custo DECIMAL(15,2) DEFAULT 0,
  estoque_atual DECIMAL(15,4) DEFAULT 0,
  estoque_minimo DECIMAL(15,4) DEFAULT 0,
  id_matriz_fiscal_icms BIGINT UNSIGNED,
  id_matriz_fiscal_pis BIGINT UNSIGNED,
  id_matriz_fiscal_cofins BIGINT UNSIGNED,
  id_matriz_fiscal_ipi BIGINT UNSIGNED,
  FOREIGN KEY (id_empresa) REFERENCES tb_empresa(id_empresa)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_produto_codigo (codigo_interno),
  INDEX idx_produto_barras (codigo_barras)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

*(Demais tabelas seguem padrões similares, ver scripts completos na pasta `/db/ddl` do projeto.)*

---

## 7. Estratégia de Versionamento do Modelo

- Cada alteração relevante de schema deverá gerar um arquivo **`Vxxx__descricao.sql`** na pasta de migrações (por exemplo, usando **Phinx**, **Laravel Migrations** ou ferramenta equivalente).  
- A versão do banco em produção deve ser rastreada em tabela própria (`tb_schema_version`).  
- Toda mudança na **Especificação Funcional Detalhada (EFD)** que impactar dados deve ser refletida neste manual e em scripts de migração correspondentes.

---

## 8. Considerações de Segurança

1. **Segregação de acesso**  
   - Banco acessível apenas pela aplicação e por usuários de manutenção autorizados.  

2. **Criptografia**  
   - Senhas de usuários: hash forte (bcrypt/argon2) – não armazenar em texto puro.  
   - Senhas de certificados: armazenar criptografadas (ex.: libsodium/OpenSSL) com chave fora do repositório.  

3. **LGPD**  
   - Dados pessoais (CPF, telefone, e-mail) devem ser usados apenas para fins fiscais/contratuais.  
   - Implementar rotina de anonimização em backups antigos quando aplicável.  

---

## 9. Próximos Passos

1. Refinar este ERD junto com o contador da empresa, ajustando regras específicas de ICMS, PIS/COFINS, ISS e regimes especiais.  
2. Validar a compatibilidade com as **tags do XML NFe/NFCe/NFSe** em conjunto com o documento “Guia de Integração SEFAZ (técnico)”.  
3. A partir deste modelo, gerar os scripts de criação e de migração para os ambientes **DEV**, **HOMOLOGAÇÃO** e **PRODUÇÃO**.  
4. Sincronizar este ERD com a **Especificação Funcional Detalhada (EFD)** para garantir que todos os requisitos estejam cobertos.

---

Este **Manual de Modelagem de Dados (ERD)** é a base técnica para implementação do banco MySQL e deve ser revisado conjuntamente por Arquitetura, Desenvolvimento e Contabilidade antes do início da codificação.
