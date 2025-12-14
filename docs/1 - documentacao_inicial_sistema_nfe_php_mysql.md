
# Documento de Visão e Arquitetura Inicial  
## Sistema de Emissão de NF-e (Modelo 55) em PHP + MySQL

> Versão: 0.1  
> Data: 30/11/2025  
> Responsável: Team Leader / Arquitetura

---

## 1. Objetivo do documento

Este documento tem como objetivo definir a **visão inicial**, **escopo funcional**, **arquitetura técnica** e **diretrizes de desenvolvimento** de um sistema completo de emissão de **Nota Fiscal Eletrônica (NF-e – modelo 55)**, a ser desenvolvido em **PHP** utilizando **MySQL** como banco de dados relacional.

Ele servirá como:

- Base para alinhamento entre **negócio**, **desenvolvimento**, **infraestrutura** e **fiscal/contábil**.  
- Referência inicial para detalhamento posterior de requisitos funcionais, técnicos e de conformidade fiscal.  
- Guia de implementação para a equipe de desenvolvimento (backend, frontend, QA e DevOps).

---

## 2. Visão geral do sistema

O sistema será uma aplicação **web** para:

- Cadastrar empresas emissoras de NF-e (multi-empresa).  
- Cadastrar clientes (destinatários), produtos/serviços, CFOP, NCM, CST/CSOSN, alíquotas e demais parâmetros fiscais.  
- Gerar, assinar digitalmente, transmitir e gerenciar NF-e junto às SEFAZ estaduais.  
- Emitir DANFe (PDF) para impressão/envio ao cliente.  
- Gerenciar cancelamentos, cartas de correção, inutilizações e eventos relacionados.  
- Armazenar e permitir o download dos arquivos **XML** de cada NF-e.  
- Gerar relatórios gerenciais e fiscais.

### 2.1 Público-alvo

- Pequenas e médias empresas que necessitam emitir NF-e modelo 55.  
- Escritórios contábeis que desejam operar múltiplos CNPJs num único sistema.  

### 2.2 Características principais

- **Multi-empresa** e **multi-usuário**.  
- Arquitetura modular para futura expansão (NFC-e, NFSe, CT-e etc).  
- Conformidade com o layout de NF-e versão 4.0 (a confirmar conforme UF).  
- Suporte a diferentes ambientes: **Homologação** e **Produção**.  

---

## 3. Tecnologias e stack

### 3.1 Backend

- Linguagem: **PHP 8.x** (mínimo 8.1)  
- Framework sugerido: **Laravel** ou **Symfony** (caso o projeto opte por framework full-stack)  
  - Para este documento, assumiremos **Laravel** pela produtividade.  
- Padrões:
  - MVC
  - DI/Service Container
  - Repositórios/Services para regras de negócio

### 3.2 Frontend

- Blade (Laravel) ou outra engine de templates.  
- HTML5, CSS3, JavaScript (com uso moderado de framework – ex.: Vue.js ou apenas Alpine.js).  
- Design responsivo (Bootstrap ou Tailwind).

### 3.3 Banco de dados

- **MySQL 8.x**  
- Uso de **migrations** e **seeders** (Laravel) para versionamento de esquema.  
- Índices planejados para chaves de pesquisa (CNPJ, número da nota, chave de acesso, etc.).

### 3.4 Integrações externas

- **Webservices SEFAZ** (autorização, consulta, inutilização, cancelamento, eventos etc.).  
- Biblioteca/SDK para **certificados digitais A1/A3** (ex.: `phpseclib` + wrappers específicos NF-e).  
- Serviço de geração de **PDF** (DANFe) – por exemplo `dompdf` ou `snappy`.

### 3.5 Infraestrutura

- Servidor web: Nginx ou Apache.  
- Sistema operacional: Linux (Ubuntu Server ou similar).  
- Versionamento: Git (GitHub/GitLab).  
- CI/CD: GitHub Actions / GitLab CI (pipeline de build+test+deploy).  

---

## 4. Módulos funcionais

### 4.1 Autenticação e controle de acesso

- Login com e-mail + senha (futuro suporte MFA).  
- Recuperação de senha via e-mail.  
- Perfis de usuário:
  - **Administrador do sistema** (nível global).  
  - **Administrador da empresa** (nível CNPJ).  
  - **Operador fiscal** (emissão de notas).  
  - **Consulta/Financeiro** (acesso somente leitura a relatórios).  
- RBAC (Role-Based Access Control) aplicado no backend e frontend (menus e rotas).

### 4.2 Gestão de empresas (emitentes)

- Cadastro de empresa com:
  - CNPJ, razão social, nome fantasia  
  - CNAE principal, regime tributário (Simples, Lucro Presumido, Lucro Real, MEI – se aplicável)  
  - Endereço, contatos  
- Parâmetros fiscais por empresa:
  - Tipo de regime de ICMS  
  - CST/CSOSN padrão para operações mais comuns  
  - CFOP mais utilizados  
  - Percentuais de ICMS, PIS, COFINS, IPI, FCP etc.  
- Configurações de emissão:
  - **Séries de NF-e** (numeração, ambiente, tipo de operação)  
  - Ambiente padrão (homologação / produção)  
  - Tipo de emissão (normal, contingência)

### 4.3 Certificados digitais

- Cadastro de certificado digital **A1** (arquivo .pfx) + senha.  
- Armazenamento seguro (criptografado) do arquivo e da senha no banco/sistema de arquivos.  
- Tela de teste de certificado (validar datas de validade, CNPJ do certificado, etc.).  

### 4.4 Cadastros básicos

#### 4.4.1 Clientes (destinatários)

- Pessoas físicas e jurídicas.  
- Campos obrigatórios:
  - CPF/CNPJ  
  - Nome/Razão social  
  - Endereço completo (logradouro, número, bairro, CEP, município, UF, país)  
  - Inscrição estadual (quando aplicável)  
- Tipos de cliente: consumidor final, contribuinte de ICMS etc.  

#### 4.4.2 Produtos / Serviços

- Código interno  
- Descrição  
- NCM / NBS  
- CFOP padrão (saída / entrada)  
- Unidade de medida  
- Preço unitário  
- Controle de estoque (quantidade disponível) – opcional.  
- Parâmetros fiscais por produto:
  - Origem da mercadoria  
  - CST/CSOSN de ICMS  
  - CST de PIS/COFINS  
  - Alíquotas de ICMS, PIS, COFINS, IPI, etc.  

#### 4.4.3 Tabelas fiscais auxiliares

- CFOP  
- NCM  
- CSOSN/CST  
- Códigos de benefício fiscal (se aplicável à UF)  

Estas tabelas poderão ser pré-carregadas por meio de scripts de importação.

### 4.5 Emissão de NF-e

#### 4.5.1 Fluxo de emissão

1. Usuário seleciona a **empresa emissora**.  
2. Usuário seleciona **cliente (destinatário)**.  
3. Usuário adiciona **itens** (produtos/serviços) com:
   - quantidade  
   - valor unitário  
   - descontos/acréscimos  
4. Sistema calcula automaticamente:
   - Base de cálculo de ICMS, PIS, COFINS, IPI etc.  
   - Valores de impostos conforme regras configuradas por empresa/produto.  
5. Usuário define:
   - Tipo de operação (entrada/saída)  
   - Natureza da operação (texto + vínculo CFOP)  
   - Forma de pagamento (à vista, prazo, cartão, boleto etc.)  
   - Transporte (transportadora, frete por conta de quem, volumes etc.)  
6. Sistema gera:
   - XML preliminar da NF-e (layout 4.0).  
7. Usuário confirma e **transmite**:
   - XML é assinado com certificado digital.  
   - Envio para o webservice da SEFAZ (UF do emissor) – ambiente homologação/produção.  
8. Resposta SEFAZ:
   - Em caso de **autorização**, gravar protocolo, status e chave de acesso.  
   - Em caso de **rejeição**, registrar o motivo e exibir mensagem amigável ao usuário.  

#### 4.5.2 Estados da NF-e no sistema

- **Rascunho** – NF-e ainda não gerada/transmitida (armazenada só no banco).  
- **Em validação** – XML gerado e em processo de assinatura/transmissão.  
- **Autorizada** – SEFAZ autorizou uso da NF-e (status 100/150 etc).  
- **Denegada** – SEFAZ denegou a emissão para o emitente/destinatário.  
- **Cancelada** – NF-e autorizada, posteriormente cancelada dentro do prazo legal.  
- **Inutilizada** – número inutilizado a pedido do contribuinte.  

### 4.6 DANFe (PDF)

- Geração do DANFe a partir do XML autorizado.  
- Layout conforme manual da NF-e (modelo paisagem retrato A4).  
- Permitir:
  - Download em PDF  
  - Impressão direta  
  - Envio por e-mail ao cliente (com XML em anexo).

### 4.7 Cancelamento de NF-e

- Tela para seleção da NF-e autorizada.  
- Inserção de **justificativa** (mínimo de caracteres conforme legislação).  
- Envio do evento de cancelamento à SEFAZ.  
- Atualização do status da nota + registro de protocolo de cancelamento.  

### 4.8 Carta de Correção Eletrônica (CC-e)

- Tela para criar evento de CC-e vinculado a uma NF-e autorizada.  
- Registro das correções permitidas pela legislação.  
- Envio do evento à SEFAZ + armazenamento do XML de evento.  

### 4.9 Inutilização de numeração

- Tela para informar:
  - Série  
  - Faixa de numeração (inicial/final)  
  - Ano de inutilização  
  - Justificativa  
- Envio de pedido de inutilização à SEFAZ.  
- Registro do protocolo e armazenamento do XML de resposta.

### 4.10 Armazenamento de XML

Para cada NF-e e evento (cancelamento, CC-e, inutilização):

- Armazenar o **XML original** autorizado/registrado.  
- Indexar por:
  - Empresa  
  - Série  
  - Número  
  - Chave de acesso  
  - Data de emissão  

Possibilidade de download em lote (zip) por período.

### 4.11 Relatórios

- **Relatório de NF-e emitidas** por:
  - Período  
  - Empresa  
  - Cliente  
  - Situação (autorizada, cancelada, denegada etc.)  
- **Relatório de impostos** (ICMS, PIS, COFINS, IPI etc) por período.  
- Exportação para:
  - CSV / Excel  
  - PDF  

---

## 5. Requisitos não funcionais

### 5.1 Segurança

- Criptografia de senhas (bcrypt/argon2).  
- Armazenamento seguro de certificados (criptografia em repouso).  
- HTTPS obrigatório em produção.  
- Controle de sessão com expiração e logout automático.  
- Logs de acessos e ações sensíveis (auditoria).  
- Conformidade com **LGPD**:
  - Termo de consentimento  
  - Política de privacidade  
  - Tratamento mínimo de dados pessoais.

### 5.2 Performance e escalabilidade

- Banco de dados com índices adequados (CNPJ, chave de acesso, datas).  
- Paginação em listas de notas/clientes/produtos.  
- Cache de dados de tabelas fiscais pouco mutáveis (CFOP, NCM etc).  
- Possibilidade de horizontalização futura (separação web/app/DB).

### 5.3 Disponibilidade e backup

- Backups automáticos diários do banco de dados.  
- Backup dos XMLs gerados (armazenamento em diretório dedicado ou storage externo).  
- Estratégia de retenção de dados compatível com exigências legais (mín. 5 anos para NF-e).

### 5.4 Qualidade de código

- Padrão PSR (PSR-1, PSR-2/12).  
- Testes automatizados:
  - Testes unitários para regras de negócio.  
  - Testes de integração para comunicação com SEFAZ.  
- Análise estática (PHPStan/Psalm).  

---

## 6. Modelo de dados inicial (alto nível)

> Detalhamento a ser refinado em um documento exclusivo de **Modelagem de Dados**. Abaixo, as principais tabelas.

### 6.1 Tabelas principais

- `users`
  - id, name, email, password_hash, status, created_at, updated_at

- `roles`
  - id, name, description

- `user_role`
  - user_id, role_id

- `companies` (empresas emissoras)
  - id, razao_social, nome_fantasia, cnpj, ie, im, cnae, regime_tributario, endereco_id, telefone, email, status

- `company_settings`
  - id, company_id, ambiente_padrao, serie_padrao, tipo_emissao, ...  

- `company_certificates`
  - id, company_id, alias, tipo, caminho_arquivo, senha_criptografada, valido_de, valido_ate, ativo

- `addresses`
  - id, logradouro, numero, bairro, cep, cidade, uf, pais, complemento

- `customers`
  - id, company_id, tipo_pessoa, cpf_cnpj, nome_razao, ie, email, telefone, endereco_id, contribuinte_icms

- `products`
  - id, company_id, codigo_interno, descricao, ncm, cfop_padrao, unidade, preco_unitario, origem_mercadoria, estoque_atual

- `product_tax_configs`
  - id, product_id, cst_icms, csosn_icms, aliq_icms, aliq_pis, aliq_cofins, cst_pis, cst_cofins, aliq_ipi, cst_ipi

- `nfe_series`
  - id, company_id, numero_serie, descricao, ambiente, tipo_emissao, numero_atual, status

- `nfe_headers`
  - id, company_id, nfe_series_id, numero_nfe, chave_acesso, modelo, data_emissao, data_saida, tipo_operacao, natureza_operacao, cliente_id, total_produtos, total_nfe, status_sefaz, protocolo_sefaz, xml_path, ambiente

- `nfe_items`
  - id, nfe_id, product_id, descricao, cfop, ncm, quantidade, valor_unitario, valor_total, ... campos de impostos por item

- `nfe_events`
  - id, nfe_id, tipo_evento (CANCELAMENTO, CCE, INUTILIZACAO), protocolo, justificativa, data_evento, xml_path, status

- `nfe_inutilizations`
  - id, company_id, serie, numero_inicial, numero_final, justificativa, ano, protocolo, status, xml_path

- `logs_auditoria`
  - id, user_id, company_id, acao, descricao, ip, user_agent, created_at

Outras tabelas auxiliares poderão ser criadas para CFOP, NCM, CST, etc.

---

## 7. Integração com SEFAZ – Visão técnica

### 7.1 Abstração de UF

- Criação de um módulo de integração que **isola** as diferenças de URL, certificados e timeouts por UF:  
  - `SefazClientInterface`  
  - Implementações por ambiente: `SefazClientHomologacao`, `SefazClientProducao`

### 7.2 Fluxo técnico de autorização

1. Obter configurações da empresa (ambiente, série, certificado).  
2. Gerar XML conforme layout 4.0.  
3. Assinar digitalmente XML (tag `infNFe`).  
4. Validar XML contra XSD oficial da NF-e.  
5. Montar envelope SOAP e enviar para SEFAZ.  
6. Tratar resposta:
   - Códigos de retorno, mensagens e protocolos.  
7. Atualizar status da NF-e e persistir XML+retorno.

### 7.3 Tratamento de erros

- Rejeições por erro de schema / campos obrigatórios.  
- Rejeições fiscais específicas (CFOP inválido, CST incompatível, etc.).  
- Falhas de comunicação (timeout, indisponibilidade SEFAZ).  
- Registro detalhado em log técnico + log de auditoria.

---

## 8. Fluxos de uso principais (alto nível)

### 8.1 Onboarding de nova empresa

1. Administrador do sistema cria conta de usuário administrador da empresa.  
2. Usuário acessa o sistema e cadastra:
   - Dados básicos da empresa  
   - Endereço e regime tributário  
   - Certificado digital A1  
   - Séries de NF-e (homologação e produção)  
3. Usuário importa/cadastra:
   - Tabela de produtos  
   - Cadastro de clientes  
4. Usuário emite primeiras notas em **ambiente de homologação** para teste.  

### 8.2 Emissão de NF-e em produção

1. Usuário seleciona ambiente **Produção**.  
2. Cria nova NF-e, preenche dados e itens.  
3. Sistema valida dados (front + back).  
4. Usuário clica em **Transmitir**.  
5. SEFAZ autoriza; sistema salva XML e gera DANFe.  
6. Sistema permite download/envio de PDF + XML.

---

## 9. Roadmap de implementação (macro)

### Fase 1 – Fundamentos (MVP técnico)

- Setup do repositório, CI/CD e ambientes.  
- Implementação de:
  - Autenticação e gestão básica de usuários e empresas.  
  - Cadastro de clientes e produtos.  
  - Módulo de certificados digitais.  
  - Emissão de NF-e em modo **homologação** com:
    - Geração de XML  
    - Assinatura digital  
    - Envio à SEFAZ  
    - Armazenamento de XML e status.  

### Fase 2 – Funcionalidades fiscais avançadas

- Cancelamento, CC-e, inutilização de numeração.  
- Melhorias em cálculo de impostos por regime tributário.  
- Geração de DANFe (PDF).  

### Fase 3 – Usabilidade e relatórios

- Dashboard inicial (resumo de notas emitidas, valores, status).  
- Relatórios fiscais e gerenciais com exportação CSV/PDF.  
- Módulo de logs de utilização / auditoria detalhada.

### Fase 4 – Hardening e conformidade

- Revisão de segurança, LGPD e políticas de backup.  
- Testes de carga e performance.  
- Documentação para usuários finais e equipe de suporte.

---

## 10. Próximos documentos a serem criados

1. **Especificação Funcional Detalhada** (EFD)  
2. **Manual de Modelagem de Dados (ERD)**  
3. **Guia de Integração SEFAZ (técnico)**  
4. **Manual do Usuário (operacional)**  
5. **Plano de Testes e Cenários de Homologação com Contabilidade**

---

> Este documento de visão e arquitetura inicial deve ser revisado e aprovado (produto, fiscal, tecnologia) antes do início do desenvolvimento efetivo. A partir dele serão derivados os artefatos de especificação detalhada e os épicos/estórias que irão compor o backlog do projeto.
