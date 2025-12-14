
# Especificação Funcional Detalhada (EFD)  
## Sistema de Emissão de NF-e – SMARTENG / SSA

> Versão: 0.1  
> Data: 30/11/2025  
> Responsável: Product Manager / Team Leader (com apoio de ChatGPT – documentação inicial)

---

## 1. Objetivo do Documento

Esta Especificação Funcional Detalhada (EFD) descreve **o que** o sistema de emissão de NF-e deve fazer do ponto de vista funcional, sem entrar nos detalhes de implementação de código.

O sistema será desenvolvido em **PHP** com **MySQL**, e deverá ser capaz de:

- Cadastrar empresas, clientes, produtos/serviços e parâmetros fiscais.  
- Emitir e gerenciar **NF-e**, **NFC-e**, **NFS-e** e **CT-e** (escopo expansível).  
- Integrar-se com os **webservices da SEFAZ** para autorização, cancelamento, inutilização e consulta de notas.  
- Gerar DANFE / DANF-e em PDF ou impressão direta.  
- Manter trilhas de auditoria, relatórios e integração contábil.

---

## 2. Escopo Funcional do Sistema

### 2.1. Módulos Principais

1. **Autenticação e Perfis de Acesso**
   - Login do usuário (e-mail + senha).
   - Recuperação de senha.
   - Perfis: *Administrador*, *Operador Fiscal*, *Operador de Vendas*, *Contabilidade* (somente leitura com alguns poderes específicos).
   - Controle de permissões por módulo e tipo de operação (criar, editar, excluir, consultar, transmitir).

2. **Gestão de Empresas (Emitentes)**
   - Cadastro de CNPJ emitente (empresa principal) e filiais.
   - Dados cadastrais completos (razão social, fantasia, endereço, CNAE, regime tributário, CRT, IE, IM, etc.).
   - Configuração de certificados digitais (A1 inicialmente, evolutivo para A3).
   - Parametrização do ambiente de emissão (homologação / produção).
   - Parametrização de séries e numeração por tipo de documento (NF-e, NFC-e, NFS-e, CT-e).

3. **Cadastros Auxiliares**
   - **Clientes/Destinatários** (pessoa física e jurídica).
   - **Produtos e Serviços** (com toda a parametrização fiscal).
   - **Transportadoras**.
   - **Natureza de Operação (CFOP)**.
   - **Matriz Fiscal** (regras de ICMS, PIS, COFINS, IPI, ISS etc.).
   - **Usuários internos** (vinculados à empresa emitente).

4. **Emissor Fiscal**
   - Emissão de:
     - NF-e (modelos 55).
     - NFC-e (modelo 65).
     - NFS-e (modelo municipal, começando por Santos, SP).
     - CT-e (modelo 57) – opcional / fase posterior.
   - Rotinas:
     - Inclusão / edição / exclusão (enquanto não transmitida).
     - Transmissão para SEFAZ.
     - Impressão de DANFE / DANF-e / RPS / etc.
     - Cancelamento / carta de correção / inutilização.
     - Consulta de status na SEFAZ.
     - Manifestação do destinatário (futuro).

5. **Configurações e Ajustes**
   - Matrizes fiscais.
   - Naturezas de operação.
   - Configurações de numeração e série.
   - Configuração de webservices (URLs por UF e ambiente).
   - Parametrização de timeout, tentativas, contingência offline/online.
   - Gestão de logs e auditoria.

6. **Relatórios**
   - Relatório de produtos.
   - Relatório de clientes.
   - Relatório de estoque (quando aplicável).
   - Relatório de NF emitidas (por período, CFOP, cliente, status).
   - Exportações em CSV, Excel e PDF.

7. **Ajuda e Suporte**
   - FAQ e links para central de ajuda.
   - Links de contato (WhatsApp, e-mail de suporte, telefone 0800).
   - Acesso rápido para “Manual do Usuário” (PDF/HTML).

---

## 3. Personas e Perfis de Usuário

### 3.1. Personas

1. **Administrador da Empresa (Sergio / dono de empresa)**
   - Responsável por configurar a empresa, cadastrar usuários e acompanhar relatórios.
   - Necessidade: visão consolidada de notas emitidas, segurança na emissão fiscal, evitar autuações.

2. **Operador Fiscal / Administrativo**
   - Usuário que emite notas no dia-a-dia.
   - Necessidade: fluxo rápido e simples para emissão, com o mínimo de erro possível.

3. **Contador / Escritório de Contabilidade**
   - Acessa notas emitidas, exporta XML e relatórios.
   - Necessidade: padrão consistente, facilidade de exportação, integração com o sistema contábil.

4. **Suporte Técnico Interno**
   - Responsável por apoiar usuários, ajustar configurações fiscais e corrigir cadastros.
   - Necessidade: acesso a logs, trilha de auditoria e parametrizações detalhadas.

### 3.2. Matriz Inicial de Permissões por Perfil

| Módulo / Ação                        | Admin | Operador Fiscal | Operador Vendas | Contabilidade |
|-------------------------------------|:-----:|:----------------:|:----------------:|:-------------:|
| Gerenciar Empresas (CNPJ)          |  C/R/U/D  |   R   |   R   |   R   |
| Usuários & Permissões              |  C/R/U/D  |   -   |   -   |   -   |
| Cadastros de Clientes              |  C/R/U/D  | C/R/U/D | C/R/U |  R   |
| Cadastros de Produtos/Serviços     |  C/R/U/D  | C/R/U/D |  R   |  R   |
| Matriz Fiscal / Natureza Operação  |  C/R/U/D  |   R   |   -   |  R   |
| Emissão NF-e / NFC-e               |  C/R/U/D  | C/R/U/D |   C/R  |  R   |
| Emissão NFS-e                      |  C/R/U/D  | C/R/U/D |   C/R  |  R   |
| Cancelamento / CC-e / Inutilização |  C/R/U/D  |   C/R   |   -   |  R   |
| Relatórios                         |  C/R/U/D  |   R   |   R   |  R   |
| Logs e Auditoria                   |  C/R/U/D  |   R   |   -   |  R   |

Legenda: **C** = Criar, **R** = Ler, **U** = Atualizar, **D** = Deletar  

---

## 4. Requisitos Funcionais por Módulo

### 4.1. Autenticação e Segurança

**RF-001** – O sistema deve permitir que o usuário faça login informando e-mail e senha.  
**RF-002** – O sistema deve validar as credenciais em uma tabela `usuarios` no banco MySQL.  
**RF-003** – O sistema deve bloquear a conta do usuário após *N* tentativas falhas de login (parametrizável).  
**RF-004** – O sistema deve permitir recuperação de senha via e-mail cadastrado.  
**RF-005** – O sistema deve registrar data/hora, IP e dispositivo a cada login, armazenando em tabela de sessões.  
**RF-006** – O sistema deve exigir uso de HTTPS (camada de transporte segura).  

### 4.2. Gestão de Empresas

**RF-010** – O sistema deve permitir o cadastro de uma ou mais empresas emitentes por usuário.  
**RF-011** – Cada empresa deve possuir os seguintes dados mínimos:
- CNPJ  
- Razão Social  
- Nome Fantasia  
- Inscrição Estadual  
- Inscrição Municipal  
- CRT (Código de Regime Tributário)  
- CNAE principal  
- Endereço completo (logradouro, número, complemento, bairro, cidade, UF, CEP)  
- Telefone, e-mail, site (opcional)

**RF-012** – O sistema deve permitir configuração de ambiente: homologação / produção.  
**RF-013** – O sistema deve permitir o upload de Certificado Digital A1 (arquivo .pfx ou .p12) + senha.  
**RF-014** – O sistema deve validar o vencimento do certificado e exibir alertas com antecedência (por exemplo, 30, 15, 7 dias).  
**RF-015** – O sistema deve permitir configurar numeração de séries por tipo de documento:
- NF-e: série, número inicial, último número utilizado.  
- NFC-e: série, número inicial, último número utilizado.  
- NFS-e: série de RPS, número inicial.  
- CT-e: série, número inicial (futuro).

### 4.3. Cadastros – Clientes

**RF-020** – O sistema deve permitir cadastrar clientes (pessoa física e jurídica).  
**RF-021** – Para pessoas jurídicas, deve ser informado CNPJ, IE, razão social, fantasia e endereço.  
**RF-022** – Para pessoas físicas, deve ser informado CPF, nome completo e endereço.  
**RF-023** – O sistema deve verificar duplicidade de CNPJ/CPF e alertar o usuário.  
**RF-024** – O sistema deve permitir a pesquisa de clientes por nome, CNPJ/CPF e cidade.  
**RF-025** – O sistema deve permitir marcar um cliente como “inativo” ao invés de excluí-lo fisicamente.

### 4.4. Cadastros – Produtos e Serviços

**RF-030** – O sistema deve permitir cadastrar itens do tipo **Produto** e **Serviço**.  
**RF-031** – Campos obrigatórios:
- Código interno  
- Descrição  
- NCM (para produtos)  
- CEST (quando aplicável)  
- Unidade de comercialização  
- Preço de venda  
- CFOP padrão  
- Alíquotas fiscais (ICMS, PIS, COFINS, IPI, ISS, se aplicável)  
- Origem da mercadoria (0 a 8)

**RF-032** – O sistema deve permitir definir múltiplos preços (atacado, varejo, promocional).  
**RF-033** – O sistema deve permitir vincular um produto a uma categoria e subcategoria.  
**RF-034** – O sistema deve permitir importar lista de produtos via arquivo CSV/Excel em lote.  

### 4.5. Matriz Fiscal e Natureza de Operação

**RF-040** – O sistema deve possuir tela para cadastro da **Matriz Fiscal**, permitindo:  
- Definir regras por imposto (ICMS, PIS, COFINS, IPI, ISS, etc.).  
- Definir alíquotas por tipo de operação (dentro/fora do estado, substituição tributária, isenção, etc.).  
- Definir vigência: data de início e fim.  

**RF-041** – O sistema deve possuir tela para **Natureza de Operação**, contendo:  
- Código interno (ex: V1, V2, C1, C2, TE1…)  
- Descrição (ex: Venda dentro do estado, Compra fora do estado, Transferência, etc.)  
- Tipo de operação (entrada/saída).  
- CFOP padrão.  
- Marcador de ativo/inativo.

**RF-042** – Ao emitir um documento fiscal, o usuário deve selecionar a **Natureza de Operação**, que preencherá CFOP padrão e regras de tributação sugeridas.

### 4.6. Emissor Fiscal – NF-e / NFC-e / NFS-e / CT-e

#### 4.6.1. Criação de Documento

**RF-050** – O usuário deve poder criar uma nova NF-e a partir de:  
- Tela em branco (digitação manual);  
- Pedido de venda (futuro);  
- Duplicação de nota anterior (reutilizar cabeçalho e itens).

**RF-051** – Campos principais da NF-e:
- Emitente (empresa atual).  
- Destinatário (cliente).  
- Natureza de Operação.  
- Tipo de operação (entrada/saída).  
- Modelo (55, 65, NFS-e municipal, 57).  
- Série e número (preenchidos automaticamente com base na configuração).  
- Data/hora de emissão.  
- Tipo de pagamento (à vista, prazo, cartão, boleto, pix).  
- Itens com quantidade, valor unitário, descontos, impostos.

**RF-052** – O sistema deve calcular automaticamente os valores de impostos com base na **Matriz Fiscal** e nos parâmetros do produto/serviço.  
**RF-053** – O sistema deve permitir ao usuário editar manualmente determinados campos fiscais (somente usuários com permissão específica).  
**RF-054** – O sistema deve validar campos obrigatórios antes de permitir transmitir.

#### 4.6.2. Transmissão e Retorno da SEFAZ

**RF-060** – O sistema deve assinar digitalmente o XML antes de enviar à SEFAZ.  
**RF-061** – O sistema deve chamar o webservice de **autorização** da SEFAZ, de acordo com a UF do emitente e ambiente configurado.  
**RF-062** – O sistema deve registrar o retorno da SEFAZ (protocolo, data/hora, status, motivo).  
**RF-063** – Em caso de sucesso, a NF-e deve ser marcada com status **“Autorizada”**.  
**RF-064** – Em caso de rejeição, o sistema deve exibir a mensagem de erro da SEFAZ e manter a NF-e em status **“Rejeitada”**, permitindo correção e retransmissão.  
**RF-065** – O sistema deve manter o XML assinado e o XML de retorno armazenados em banco ou sistema de arquivos padronizado.

#### 4.6.3. Impressão / Geração de DANFE

**RF-070** – O sistema deve gerar DANFE em formato PDF para NF-e.  
**RF-071** – O sistema deve suportar impressão em impressora comum (A4) para NF-e.  
**RF-072** – Para NFC-e, o sistema deve suportar impressão em bobina (80mm) com layout simplificado.  
**RF-073** – O sistema deve permitir envio de DANFE por e-mail para o cliente.

#### 4.6.4. Cancelamento, Carta de Correção e Inutilização

**RF-080** – O sistema deve permitir o cancelamento de NF-e dentro do prazo legal, com justificativa.  
**RF-081** – O sistema deve enviar a solicitação de cancelamento à SEFAZ e armazenar o protocolo de cancelamento.  
**RF-082** – O sistema deve permitir **Carta de Correção Eletrônica (CC-e)**, armazenando o evento e XML correspondente.  
**RF-083** – O sistema deve permitir **Inutilização de numeração**, enviando para SEFAZ as faixas inutilizadas com respectiva justificativa.

### 4.7. Relatórios

**RF-090** – O sistema deve gerar relatórios com filtros por período, cliente, CFOP, status e tipo de documento.  
**RF-091** – O usuário deve poder exportar relatórios em **CSV, XLSX e PDF**.  
**RF-092** – O sistema deve disponibilizar relatório de **produtos**, **clientes** e **estoque básico** (quantidade mínima, quantidade em estoque, saldo estimado).  
**RF-093** – O sistema deve fornecer **relatório consolidado por CFOP** e **por CST**, útil para contabilidade.

### 4.8. Logs, Auditoria e Utilização

**RF-100** – O sistema deve registrar todas as ações relevantes:
- Login/logout.  
- Inclusão, edição e exclusão de cadastros.  
- Emissão, cancelamento, inutilização e CC-e.  
- Mudanças de configuração fiscal.  

**RF-101** – Os logs devem registrar: usuário, data/hora, IP, operação realizada, antes/depois (quando possível).  
**RF-102** – Deve existir tela de consulta de logs filtrando por usuário, data e tipo de operação.

### 4.9. Integração com Contabilidade

**RF-110** – O sistema deve permitir exportar em lote:
- XML de NF-e/NFC-e/NFS-e por período.  
- Relatório sintético de impostos por período.  

**RF-111** – O sistema deve permitir configurar um e-mail padrão da contabilidade para envio automático de.
- XMLs autorizados do mês anterior.  
- Relatório consolidado fechando o mês.

---

## 5. Requisitos Não Funcionais (Resumo)

> Estes pontos serão detalhados em documento próprio, mas já constam como diretrizes.

- **RNF-001 – Performance:** tempo máximo de carregamento de telas principais: 3s em ambiente padrão.  
- **RNF-002 – Escalabilidade:** arquitetura preparada para multi-empresas, multi-usuários.  
- **RNF-003 – Segurança:** uso de HTTPS, criptografia de senhas (bcrypt), proteção contra SQL Injection.  
- **RNF-004 – Disponibilidade:** meta mínima de 99% no horário comercial.  
- **RNF-005 – Rastreamento:** logs completos e exportáveis.  

---

## 6. Fluxos de Navegação

### 6.1. Menu Principal (Lateral Esquerda)

1. **Home** – visão geral/alerts.  
2. **Cadastros**  
   - Clientes  
   - Produtos/Serviços  
   - Transportadoras  
3. **Emissor Fiscal**  
   - NF-e  
   - NFC-e  
   - NFS-e  
   - CT-e (futuro)  
4. **Ajustes**  
   - Dados da empresa  
   - Configuração Fiscal / Matriz Fiscal  
   - Natureza de Operação  
   - Certificado Digital  
   - Permissões de Usuário  
   - Logs de Utilização  
5. **Relatórios**  
   - Produtos  
   - Clientes  
   - Estoque  
   - Notas Fiscais  
6. **Ajuda**  
   - FAQ  
   - Central de Ajuda (links)  
   - Suporte (WhatsApp, telefone, e-mail)

### 6.2. Fluxo Resumido – Emissão de uma NF-e

1. Usuário acessa **Emissor Fiscal → NF-e → Nova NF-e**.  
2. Seleciona **destinatário** (cliente).  
3. Seleciona **natureza de operação**.  
4. Adiciona **itens** (produtos/serviços).  
5. Confere totais e impostos calculados automaticamente.  
6. Salva NF-e em status **“Rascunho”**.  
7. Clique em **“Transmitir”**.  
8. Sistema assina XML, envia para SEFAZ e aguarda retorno.  
9. Em caso de autorização, NF-e passa a status **“Autorizada”** e é disponibilizada opção de **“Imprimir DANFE”** e **“Enviar por e-mail”**.  

---

## 7. Regras de Negócio Importantes

1. **RN-001** – Não é permitido transmitir NF-e sem certificado digital configurado e válido.  
2. **RN-002** – A numeração de NF-e deve ser sequencial por série, sem buracos, exceto se houver inutilização aprovada.  
3. **RN-003** – A alíquota de ICMS deve ser compatível com a UF de origem/destino e com o regime tributário (Simples Nacional, Lucro Presumido, Real, etc.).  
4. **RN-004** – Cancelamento de NF-e só é permitido dentro do prazo legal (ex.: 24h, a confirmar pela legislação vigente).  
5. **RN-005** – Para NFC-e, o sistema deve suportar regime de contingência em caso de indisponibilidade da SEFAZ, armazenando as notas em lote para posterior transmissão.  
6. **RN-006** – Para NFS-e, as regras variam conforme o município; inicialmente será implementado o padrão da Prefeitura de Santos, SP, e o sistema deve ser parametrizado para futuros municípios.  

---

## 8. Roadmap de Evolução (Visão Macro)

1. **Versão 1.0 – MVP Fiscal**
   - NF-e modelo 55 (emissão, cancelamento, inutilização).  
   - Cadastros básicos + matriz fiscal simplificada.  
   - Relatórios básicos.  
   - Exportação de XML para contabilidade.

2. **Versão 1.1**
   - NFC-e com impressão em bobina.  
   - Relatórios avançados (por CFOP, CST).  
   - Logs e auditoria mais detalhados.

3. **Versão 1.2**
   - NFS-e (Prefeitura de Santos).  
   - Envio automático de XMLs para contabilidade.  

4. **Versão 2.0**
   - CT-e (transporte).  
   - API pública para integração com sistemas de terceiros (ERP, lojas virtuais).  

---

## 9. Anexos e Próximos Documentos

Os próximos documentos que devem ser produzidos com base nesta EFD são:

1. **Manual de Modelagem de Dados (ERD)** – Desenho do banco MySQL (tabelas, relacionamentos, índices).  
2. **Guia de Integração SEFAZ (técnico)** – Detalhamento de webservices, URLs, métodos, schemas XML, timeouts.  
3. **Manual do Usuário (operacional)** – Passo-a-passo com prints de tela.  
4. **Plano de Testes e Cenários de Homologação com Contabilidade** – Casos de testes funcionais, fiscais e de integração.

---
