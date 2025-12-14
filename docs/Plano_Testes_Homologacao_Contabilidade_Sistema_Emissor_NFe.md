# Plano de Testes e Cenários de Homologação com Contabilidade  
_Sistema Emissor de NF-e em PHP + MySQL_

---

## 1. Objetivo do Documento

Este **Plano de Testes e Cenários de Homologação com Contabilidade** define:

- a estratégia de testes para o Sistema Emissor de NF-e;  
- os tipos de testes, ambientes, papéis e responsabilidades;  
- os **casos de teste funcionais e fiscais** (com foco em NF-e, NFC-e, NFS-e e CT-e);  
- o fluxo de **homologação junto à contabilidade e ao cliente piloto**;  
- critérios de aceite para liberação em produção.

Este documento deve ser utilizado por:

- Time de Desenvolvimento (PHP/MySQL);  
- Time de QA/Testes;  
- Contabilidade responsável (escritório parceiro);  
- Representantes de clientes piloto.

---

## 2. Escopo de Testes

### 2.1 Funcionalidades Incluídas

1. **Autenticação e Gestão de Usuários**
   - Login / Logout
   - Recuperação de senha
   - Perfis e permissões (Administrador, Operacional, Contador, Auditor)

2. **Cadastros Básicos**
   - Empresas (emitentes)
   - Clientes (destinatários)
   - Produtos e serviços
   - Tabelas auxiliares (CST, CFOP, NCM, Unidade, CST PIS/COFINS, etc.)

3. **Configurações Fiscais**
   - Parametrização geral por empresa (regime tributário, CNAE, CRT, etc.)
   - Matriz fiscal (ICMS, PIS, COFINS, IPI, ISS)
   - Natureza de operação (entrada/saída, CFOP padrão, finalidade)
   - Certificado digital (A1 / A3)
   - CSC para NFC-e (produção / homologação)
   - Parametrização NF-e / NFC-e / NFS-e / CT-e (séries, numeração, logomarca, etc.)

4. **Emissão de Documentos Fiscais**
   - NF-e (modelo 55)
   - NFC-e (modelo 65)
   - NFS-e (modelo municipal, por predefinição)
   - CT-e (modelo 57)
   - Emissão normal, em contingência e em ambiente de homologação
   - Geração de XML e DANFE/DANFCE/RPS/DACTE (PDF)

5. **Rotinas de Pós-Emissão**
   - Cancelamento de NF-e/NFC-e/NFS-e/CT-e
   - Carta de correção eletrônica (CC-e)
   - Inutilização de numeração
   - Reenvio de DANFE/ XML por e-mail
   - Download de XML (própria emissão e de terceiros, quando cabível)

6. **Controle de Estoque**
   - Baixa de estoque na emissão
   - Ajustes manuais
   - Relatório de estoque por período

7. **Relatórios**
   - Relatório de produtos
   - Relatório de clientes
   - Relatório de estoque
   - Relatórios para contabilidade (livros auxiliares, exportação para sistemas contábeis)

8. **Auditoria e Logs**
   - LOG de utilização (quem fez o quê e quando)
   - Registro de erros na comunicação com SEFAZ / prefeitura

9. **Integrações Externas**
   - SEFAZ (NFe/NFCe/CTe)
   - Prefeituras (NFS-e) – modelo padrão e variações configuradas
   - Exportação de dados (planilhas, arquivos para contabilidade)

### 2.2 Itens Fora de Escopo (neste ciclo)

- Aplicativos mobile nativos (Android/iOS)
- Integrações diretas com ERPs de terceiros (serão tratadas como projetos específicos)
- Emissor offline complexo (apenas contingência mínima via banco local está incluída)

---

## 3. Estratégia de Testes

### 3.1 Níveis de Teste

1. **Testes de Unidade (Dev)**
   - Realizados pelos desenvolvedores em PHP.
   - Foco em funções de cálculo de impostos, validações de regras de negócio e integração SEFAZ.

2. **Testes de Integração**
   - Módulos: cadastros, matriz fiscal, emissão, estoque, relatórios.
   - Integração com SEFAZ, prefeitura, envio de e-mail e armazenamento no MySQL.

3. **Testes de Sistema (QA)**
   - Cobrem fluxos ponta a ponta:
     - cadastro → parametrização → emissão → retorno SEFAZ → relatórios.

4. **Testes de Aceite do Usuário (UAT)**
   - Usuários chave do cliente piloto executam cenários pré-definidos.
   - Feedback de usabilidade, performance e aderência ao processo real.

5. **Testes de Homologação com Contabilidade**
   - Emissão de notas “de laboratório” aprovadas pela contabilidade.
   - Conferência minuciosa de cálculos e layouts, comparando com sistema fiscal já utilizado.

### 3.2 Tipos de Teste

- **Funcionais**: validação das funcionalidades descritas na Especificação Funcional.
- **Fiscais**: validação das regras tributárias específicas (CFOP, CST, base de cálculo, alíquotas, etc.).
- **Usabilidade**: facilidade de uso, clareza de mensagens e fluxo de navegação.
- **Segurança**: permissões, perfis, proteção de dados, não exposição de XMLs inadequados.
- **Performance e Carga (básica)**: resposta do sistema com volume moderado de notas.
- **Compatibilidade**: principais navegadores (Chrome, Edge, Firefox) e resoluções.
- **Regressão**: após correções e novas versões.

---

## 4. Ambientes de Teste

| Ambiente       | Descrição                                          | Certificado / CSC               | Base de Dados          |
|----------------|----------------------------------------------------|---------------------------------|------------------------|
| DEV            | Ambiente de desenvolvimento local/compartilhado   | Certificados de teste           | Base parcial/sintética |
| HOMOLOGAÇÃO    | Ambiente dedicado a QA + contabilidade            | Certificado A1 de homologação   | Cópia anonimizada      |
| PRODUÇÃO PILOTO| Ambiente real para poucos clientes selecionados    | Certificado A1 válido (produção)| Dados reais            |
| PRODUÇÃO       | Ambiente final após homologação                    | Certificado A1 válido (produção)| Dados reais            |

---

## 5. Papéis e Responsabilidades

| Papel                    | Responsabilidades principais |
|--------------------------|------------------------------|
| Product Manager          | Priorização de requisitos, aceite final de release. |
| System Architect         | Garantir aderência arquitetural, performance e segurança. |
| Desenvolvedores PHP      | Implementação, testes de unidade, suporte a QA. |
| QA / Analista de Testes  | Planejamento detalhado, execução, registro de defeitos, testes de regressão. |
| Contabilidade Parceira   | Definição de regras fiscais, validação dos cálculos e layouts, aceite fiscal. |
| Cliente Piloto           | Execução de cenários reais de uso e feedback operacional. |
| DevOps / Infra           | Configuração de ambientes, backups e deploys. |

---

## 6. Processo de Testes

1. **Planejamento**  
   - Refinar casos de teste deste documento.  
   - Definir dados de teste (CNPJs, CFOPs, NCMs, tabelas de impostos).  

2. **Preparação de Ambiente**  
   - Subir nova versão no ambiente de homologação.  
   - Configurar certificado digital de homologação e CSC de teste.  

3. **Execução de Testes de Sistema (QA)**  
   - Cobertura mínima de 80% dos casos de teste funcionais.  
   - Registro de defeitos no sistema de issues (ex: Jira, GitLab, Azure DevOps).  

4. **Correção de Defeitos (Dev)**  
   - Priorização por severidade/impacto.  
   - Geração de build corrigido para HOMOLOGAÇÃO.  

5. **Homologação com Contabilidade**  
   - Emissão de notas de teste e comparação com cálculos manuais/planilhas da contabilidade.  
   - Ajustes de regras fiscais conforme feedback.  

6. **UAT com Clientes Piloto**  
   - Execução de cenários de negócio (vendas reais em ambiente controlado).  
   - Coleta de feedback e ajustes finais.  

7. **Go/No-Go**  
   - Reunião com Product Manager + Arquitetura + QA + Contabilidade.  
   - Decisão de liberar produz̧ão piloto ou replanejar.

---

## 7. Critérios de Entrada e Saída

### 7.1 Critérios de Entrada para Homologação

- Especificação Funcional Detalhada versionada e aprovada.  
- Desenvolvimento das funcionalidades do escopo concluído.  
- Testes de unidade com taxa de sucesso ≥ 80%.  
- Ambiente de homologação configurado (banco de dados, certificados, CSC).  
- Dados de teste criados e validados pela contabilidade.

### 7.2 Critérios de Saída da Homologação

- Todos os casos de teste críticos executados.  
- 0 defeitos abertos de severidade **Crítica** ou **Alta**.  
- Defeitos **Médios** com plano de correção definido.  
- Contabilidade emite **Termo de Aceite Fiscal** para o escopo testado.  
- Clientes piloto validam os fluxos principais de venda.  

---

## 8. Gestão de Defeitos

### 8.1 Classificação de Severidade

- **Crítico**: inviabiliza emissão de notas, erros fiscais graves, falha de comunicação com SEFAZ sem contingência.  
- **Alto**: impacta operação diária, mas com possíveis contornos manuais limitados.  
- **Médio**: problemas funcionais sem impacto fiscal direto ou com alternativas claras.  
- **Baixo**: problemas cosméticos, textos, layout, usabilidade.

### 8.2 Fluxo de Tratamento

1. QA registra defeito com evidências (prints, XMLs, logs).  
2. PM prioriza de acordo com impacto e severidade.  
3. Desenvolvimento corrige e atualiza status.  
4. QA revalida no ambiente de homologação.  
5. Se aprovado, defeito é fechado; se não, retorna ao desenvolvimento.

---

## 9. Casos de Teste – Visão Geral

> Abaixo uma lista organizada de casos de teste por módulo, com foco em **homologação fiscal e operacional**.

### 9.1 Tabela de Campos

| ID | Módulo | Nome do Caso de Teste | Objetivo | Tipo | Prioridade |
|----|--------|-----------------------|----------|------|-----------|

Nas seções seguintes, cada grupo terá suas próprias tabelas.

---

## 10. Casos de Teste por Módulo

### 10.1 Autenticação e Controle de Acesso

| ID        | Caso de Teste                                         | Objetivo                                                                 | Tipo         | Resultado Esperado |
|-----------|--------------------------------------------------------|--------------------------------------------------------------------------|-------------|--------------------|
| AUTH-001  | Login com credenciais válidas                         | Garantir que usuário ativo consiga acessar o sistema                     | Funcional    | Acesso concedido e redirecionamento ao dashboard |
| AUTH-002  | Login com senha inválida                              | Validar mensagem de erro e bloqueio de acesso                           | Funcional    | Mensagem clara, sem detalhes da senha, sem login |
| AUTH-003  | Recuperação de senha por e-mail                       | Garantir envio de link de redefinição                                   | Funcional    | E-mail enviado com sucesso, link único e com expiração |
| AUTH-004  | Expiração de sessão por inatividade                   | Verificar logout automático após tempo configurado                       | Segurança    | Sessão expira e usuário é redirecionado ao login |
| AUTH-005  | Acesso a telas sem permissão                          | Confirmar que usuário sem perfil adequado é bloqueado                    | Segurança    | Acesso negado ou menu oculto, log de tentativa registrado |

### 10.2 Cadastros Básicos

#### 10.2.1 Empresas (Emitentes)

| ID        | Caso de Teste                                         | Objetivo                                                          | Tipo       | Resultado Esperado |
|-----------|--------------------------------------------------------|-------------------------------------------------------------------|-----------|--------------------|
| EMP-001   | Cadastro de empresa com dados completos               | Garantir gravação correta (CNPJ, IE, endereço, regime tributário) | Funcional | Empresa criada, visível na lista, dados gravados no banco |
| EMP-002   | Validação de CNPJ inválido                            | Rejeitar CNPJ com dígitos incorretos                             | Funcional | Mensagem de erro e campo destacado |
| EMP-003   | Alteração de dados fiscais da empresa                 | Verificar atualização de regime, CNAE, CRT                        | Funcional | Dados atualizados, histórico de alterações em log |
| EMP-004   | Seleção da empresa ativa no emissor                   | Garantir que sistema utilize parâmetros fiscais da empresa ativa  | Funcional | Emissões usam CNPJ, série, numeração da empresa selecionada |

#### 10.2.2 Clientes (Destinatários)

| ID        | Caso de Teste                                         | Objetivo                                                          | Tipo       | Resultado Esperado |
|-----------|--------------------------------------------------------|-------------------------------------------------------------------|-----------|--------------------|
| CLI-001   | Cadastro de cliente pessoa jurídica com IE e CNPJ     | Garantir gravação correta para uso na NF-e                        | Funcional | Cliente cadastrado, usado como destinatário sem erros |
| CLI-002   | Cadastro de consumidor final (CPF)                    | Garantir cadastro para NFC-e / NF-e única                         | Funcional | Cliente tipo “Consumidor Final” criado com sucesso |
| CLI-003   | Validação de CEP e endereço via serviço externo       | Verificar preenchimento automático de endereço                    | Integração | Endereço retornado corretamente ou mensagem de falha controlada |
| CLI-004   | Edição e inativação de cliente                        | Permitir correção sem perder histórico de notas                   | Funcional | Cliente inativado não pode ser usado em novas notas |

#### 10.2.3 Produtos e Serviços

| ID        | Caso de Teste                                         | Objetivo                                                          | Tipo       | Resultado Esperado |
|-----------|--------------------------------------------------------|-------------------------------------------------------------------|-----------|--------------------|
| PROD-001  | Cadastro de produto com NCM, CFOP padrão e CST        | Validar obrigatoriedade dos campos fiscais                        | Funcional | Produto salvo com dados fiscais corretos |
| PROD-002  | Produto com tributação diferenciada (ST, isento etc.) | Validar configuração conforme matriz fiscal                       | Fiscal    | Sistema seleciona regras corretas na emissão |
| PROD-003  | Importação de lista de produtos via planilha          | Testar carga em massa                                             | Integração| Produtos importados, erros reportados por linha |

---

### 10.3 Configurações Fiscais e Matriz Fiscal

| ID        | Caso de Teste                                         | Objetivo                                                        | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-----------------------------------------------------------------|-------|--------------------|
| FISC-001  | Configuração de regime tributário da empresa          | Garantir que sistema habilite campos conforme regime (Simples, Lucro) | Funcional | Parâmetros ajustados, telas se adaptam ao regime |
| FISC-002  | Cadastro de regra de ICMS por CFOP + UF + CST         | Testar armazenamento da regra na matriz                         | Fiscal | Regra utilizada automaticamente na emissão |
| FISC-003  | Cadastro de PIS/COFINS cumulativo / não cumulativo    | Validar cálculo conforme alíquota e base de cálculo             | Fiscal | Cálculos batem com planilha da contabilidade |
| FISC-004  | Matriz fiscal com vigência (DE/ATÉ)                   | Garantir uso correto de regra por período                       | Fiscal | Emissão usa regra válida para a data da nota |

---

### 10.4 Emissão de NF-e (Modelo 55)

#### 10.4.1 Fluxos Principais

| ID        | Caso de Teste                                         | Objetivo                                                        | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-----------------------------------------------------------------|-------|--------------------|
| NFE-001   | Emissão de NF-e de venda dentro do estado             | Validar fluxo completo com CFOP interno, ICMS, PIS, COFINS      | Fiscal | Autorização SEFAZ, XML e DANFE válidos |
| NFE-002   | Emissão de NF-e de venda fora do estado               | Verificar cálculo de ICMS interestadual e partilha (quando aplicável) | Fiscal | Valores conferem com contabilidade |
| NFE-003   | Emissão para consumidor final com DIFAL (se aplicável)| Validar cálculo de DIFAL e FCP                                  | Fiscal | XML aceito pela SEFAZ, cálculos corretos |
| NFE-004   | NF-e com isenção de ICMS (CST 40, 41, 50 etc.)        | Garantir correto preenchimento de campos de isenção             | Fiscal | Autorização sem rejeição, campos fiscais corretos |
| NFE-005   | NF-e com substituição tributária (ST)                 | Validar base de cálculo, MVA e valores de ST                    | Fiscal | Valores conferem com planilha da contabilidade |

#### 10.4.2 Erros e Rejeições

| ID        | Caso de Teste                                         | Objetivo                                                          | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-------------------------------------------------------------------|-------|--------------------|
| NFE-010   | Envio com CFOP incompatível com operação              | Simular rejeição da SEFAZ                                        | Fiscal | Sistema apresenta mensagem clara e guarda log de rejeição |
| NFE-011   | Falha de conexão com SEFAZ                            | Verificar comportamento em indisponibilidade                      | Integração | Sistema oferece tentativa posterior / contingência |
| NFE-012   | XML gerado com NCM inválido                           | Validar bloqueio antes do envio                                  | Fiscal | Validação local impede envio incorreto |

---

### 10.5 Emissão de NFC-e (Modelo 65)

| ID        | Caso de Teste                                         | Objetivo                                                          | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-------------------------------------------------------------------|-------|--------------------|
| NFCE-001  | Emissão de NFC-e venda balcão                         | Validar emissão rápida, impressão em bobina 80mm                 | Funcional | DANFCE impresso, autorização de uso registrada |
| NFCE-002  | NFC-e em contingência offline                         | Garantir que notas sejam numeradas e transmitidas posteriormente | Fiscal | Sincronização posterior com SEFAZ sem duplicidade |
| NFCE-003  | Uso do CSC correto (produção/homologação)             | Validar QRCode de consulta                                       | Integração | QRCode válido e apontando para portal SEFAZ de homologação/produção |

---

### 10.6 Emissão de NFS-e

| ID        | Caso de Teste                                         | Objetivo                                                          | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-------------------------------------------------------------------|-------|--------------------|
| NFSE-001  | Emissão de NFS-e com serviço de tecnologia            | Validar envio para webservice da prefeitura                       | Integração | NFS-e autorizada, RPS convertido em NFS-e |
| NFSE-002  | Tributação no município de prestação                  | Conferir cálculo de ISS com contabilidade                         | Fiscal | Valores de ISS e códigos de serviço corretos |
| NFSE-003  | Cancelamento de NFS-e                                 | Validar fluxo de cancelamento e retorno da prefeitura             | Fiscal | NFS-e cancelada e registrada no sistema |

---

### 10.7 Emissão de CT-e

| ID        | Caso de Teste                                         | Objetivo                                                     | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|--------------------------------------------------------------|-------|--------------------|
| CTE-001   | Emissão de CT-e de transporte rodoviário             | Testar cálculo de ICMS e emissão do DACTE                    | Fiscal | Autorização SEFAZ, DACTE gerado corretamente |
| CTE-002   | Vinculação de NF-es transportadas                     | Verificar vinculação de documentos de carga                  | Funcional | NF-es associadas corretamente ao CT-e |

---

### 10.8 Pós-Emissão (Cancelamento, CC-e, Inutilização)

| ID        | Caso de Teste                                         | Objetivo                                                        | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-----------------------------------------------------------------|-------|--------------------|
| POS-001   | Cancelamento de NF-e dentro do prazo legal            | Validar envio do evento de cancelamento                        | Fiscal | Evento autorizado, status da nota atualizado |
| POS-002   | Carta de Correção Eletrônica (CC-e)                   | Verificar campos permitidos para correção                      | Fiscal | CC-e aceita pela SEFAZ, XML de evento armazenado |
| POS-003   | Inutilização de numeração de NF-e                     | Testar envio de inutilização e registro no sistema             | Fiscal | Número inutilizado não pode ser utilizado em nova nota |

---

### 10.9 Controle de Estoque

| ID        | Caso de Teste                                         | Objetivo                                                        | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-----------------------------------------------------------------|-------|--------------------|
| EST-001   | Baixa de estoque na emissão de NF-e                   | Garantir redução da quantidade em estoque                       | Funcional | Estoque atualizado conforme quantidade faturada |
| EST-002   | Estorno de estoque em cancelamento de NF-e            | Verificar retorno de itens ao estoque                           | Funcional | Quantidade estornada corretamente |
| EST-003   | Relatório de estoque por período                      | Conferir integração entre movimento e relatórios                | Funcional | Saldos batem com movimentações de notas |

---

### 10.10 Relatórios e Exportação para Contabilidade

| ID        | Caso de Teste                                         | Objetivo                                                        | Tipo   | Resultado Esperado |
|-----------|--------------------------------------------------------|-----------------------------------------------------------------|-------|--------------------|
| REL-001   | Relatório de faturamento por período e CFOP           | Validar filtros e somatórios                                   | Funcional | Totais batem com planilhas da contabilidade |
| REL-002   | Exportação de dados para sistema contábil (CSV/TXT)   | Verificar layout acordado com escritório contábil               | Integração | Arquivo importado com sucesso no sistema contábil parceiro |
| REL-003   | Relatório de clientes e produtos                      | Conferir consistência dos dados de base                         | Funcional | Listagens corretas, sem duplicidades indevidas |

---

### 10.11 Segurança, Logs e Auditoria

| ID        | Caso de Teste                                         | Objetivo                                                        | Tipo       | Resultado Esperado |
|-----------|--------------------------------------------------------|-----------------------------------------------------------------|-----------|--------------------|
| SEG-001   | Log de login/logout                                   | Garantir registro de acessos                                    | Segurança | Logs gravados com usuário, IP, data/hora |
| SEG-002   | Log de emissão e cancelamento                         | Garantir rastreabilidade de quem emitiu/cancelou cada nota      | Segurança | Logs completos disponíveis para auditoria |
| SEG-003   | Acesso de contador às empresas autorizadas            | Verificar que contador só acessa dados de CNPJs liberados       | Segurança | Acesso restrito, tentativa de acesso indevido é bloqueada |

---

## 11. Cenários de Homologação com Contabilidade

> Esta seção detalha **roteiros completos** que devem ser executados em conjunto com o escritório contábil para validar a aderência fiscal do sistema.

### 11.1 Cenário H1 – Venda Interna de Mercadorias (Lucro Presumido/Real)

1. Configurar empresa de testes com regime **Lucro Presumido/Real**.  
2. Cadastrar produto com ICMS, PIS e COFINS tributados.  
3. Emitir NF-e de venda dentro do estado com CFOP apropriado (ex: 5.102).  
4. Validar:
   - base de cálculo de ICMS, PIS, COFINS;  
   - alíquotas e valores calculados;  
   - campos fiscais do XML (CST, CSOSN, CFOP, NCM).  
5. Contabilidade confere em planilha própria e emite parecer “OK” ou lista divergências.

### 11.2 Cenário H2 – Venda Interestadual com DIFAL (se aplicável)

1. Emitir NF-e de venda para consumidor final em outro estado.  
2. Validar:
   - cálculo de DIFAL (ICMS origem/destino);  
   - FCP, se houver;  
   - observações fiscais obrigatórias.  
3. Contabilidade verifica se os valores e contas contábeis estão corretos.

### 11.3 Cenário H3 – Simples Nacional (Comércio)

1. Configurar empresa em regime **Simples Nacional**.  
2. Emitir NF-e de venda de mercadoria com CSOSN correspondente.  
3. Conferir:
   - destaque ou não de ICMS conforme legislação;  
   - campos de informação complementar exigidos;  
   - enquadramento correto no Anexo do Simples.  

### 11.4 Cenário H4 – Prestação de Serviços e NFS-e

1. Cadastrar empresa prestadora de serviços.  
2. Configurar código de serviço municipal conforme CNAE.  
3. Emitir RPS e converter em NFS-e em ambiente de homologação da prefeitura.  
4. Contabilidade valida:
   - cálculo do ISS;  
   - retenções, quando houver (INSS, IRRF, PIS/COFINS, CSLL);  
   - campos obrigatórios do XML/arquivo retornado.

### 11.5 Cenário H5 – NF-e com Substituição Tributária

1. Cadastrar produto com ST, usando MVA e alíquota de ICMS_ST parametricados na matriz fiscal.  
2. Emitir NF-e com CFOP de substituição.  
3. Contabilidade confere base de cálculo e valor do ICMS próprio e do ST.  

### 11.6 Cenário H6 – Devolução de Mercadorias

1. Emitir nota de devolução de compra (entrada) com CFOP adequado (ex: 1.201, 1.202).  
2. Conferir se CST/CSOSN e bases de cálculo foram refletidos corretamente.  
3. Contabilidade avalia efeitos no livro de entradas e apuração de impostos.

### 11.7 Cenário H7 – Cancelamento e Carta de Correção

1. Emitir uma NF-e de teste.  
2. Cancelar dentro do prazo legal; contabilidade verifica se registros se encaixam na apuração.  
3. Emitir NF-e semelhante e, em vez de cancelar, enviar CC-e para corrigir campo permitido.  
4. Contabilidade valida se tratamento está conforme orientação da SEFAZ e legislação local.

### 11.8 Cenário H8 – Relatórios para Fechamento Fiscal Mensal

1. Em ambiente de homologação, emitir conjunto de notas (venda, devolução, serviço).  
2. Gerar relatórios e/ou arquivos de exportação para a contabilidade.  
3. Escritório contábil importa/lança em seu sistema e verifica se:
   - base de ICMS, PIS, COFINS, ISS, IRPJ, CSLL, Simples etc. batem com as notas;  
   - não há divergência de CFOP, CST, CSOSN, códigos de serviço.

---

## 12. Riscos e Premissas

### 12.1 Riscos

- Alterações frequentes na legislação fiscal podem invalidar regras recentemente homologadas.  
- Instabilidades nos webservices da SEFAZ e prefeituras podem atrasar testes.  
- Falta de disponibilidade da contabilidade para rodar cenários completos dentro do cronograma.

### 12.2 Premissas

- Escritório contábil parceiro está alinhado com o escopo e cronograma de testes.  
- Todos os certificados digitais de homologação estão válidos durante o período de testes.  
- Ambiente de homologação é representativo da infra de produção (PHP, MySQL, webserver, cache).

---

## 13. Indicadores de Qualidade

- **Índice de sucesso dos testes funcionais**: % de casos executados com sucesso.  
- **Índice de defeitos encontrados em produção piloto**: deve tender a zero após alguns ciclos.  
- **Tempo médio de correção de defeitos críticos**.  
- **Quantidade de divergências fiscais apontadas pela contabilidade após o go-live**.

---

## 14. Aprovações

| Papel                  | Nome / Responsável         | Assinatura / Data |
|------------------------|----------------------------|-------------------|
| Product Manager        |                            |                   |
| System Architect       |                            |                   |
| Líder de Desenvolvimento |                         |                   |
| QA / Testes            |                            |                   |
| Contador Responsável   |                            |                   |

---

Este plano deve ser revisado sempre que houver **mudanças significativas na legislação**, no **escopo do sistema** ou em **integrações com SEFAZ/Prefeituras**.  
Ele é a base para garantir que a solução seja **fiscalmente correta, operacionalmente estável** e confiável para os clientes do emissor de NF-e.
