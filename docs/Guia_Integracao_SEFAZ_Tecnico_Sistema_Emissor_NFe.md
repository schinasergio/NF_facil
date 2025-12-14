# Guia de Integração SEFAZ (Técnico)  
_Sistema Emissor de NF-e em PHP com MySQL_

---

## 1. Objetivo do Documento

Este guia tem como objetivo padronizar a **integração técnica** entre o Sistema Emissor de NF-e e os **Web Services da SEFAZ** (e, quando aplicável, Sefaz Virtual – SVRS/SVC).  

Ele deve ser utilizado por:

- Desenvolvedores PHP responsáveis pela camada de integração.
- Arquitetos de software.
- Equipe de infraestrutura/DevOps (configuração de SSL, certificados, firewall).
- Contabilidade/consultoria fiscal para validação dos fluxos fiscais.

---

## 2. Escopo da Integração

A versão inicial do sistema deve suportar:

1. **NF-e Modelo 55 – Versão 4.00**
2. Ambientes:
   - **Homologação** (testes)
   - **Produção**
3. Operações de integração:
   - Envio de NF-e em lote.
   - Consulta de recibo de lote.
   - Consulta de protocolo de NF-e.
   - Inutilização de numeração.
   - Cancelamento de NF-e.
   - Carta de Correção Eletrônica (CC-e).
   - Consulta cadastro contribuinte.
   - Distribuição de DF-e (download de XML autorizados).

Fora de escopo na Fase 1 (podem ser incluídos em fases futuras):

- NFC-e (Modelo 65).
- CT-e, MDF-e.
- NFS-e (padrões municipais).

---

## 3. Visão Geral da Arquitetura de Integração

### 3.1 Componentes Envolvidos

- **Aplicação Web PHP (Backend)**  
  Responsável por:
  - Receber requisições do front-end (emissão, cancelamento, etc.).
  - Persistir dados fiscais em MySQL.
  - Orquestrar chamadas aos serviços da SEFAZ.
  - Gerenciar filas de envio e reenvio.

- **Camada de Integração SEFAZ (Módulo PHP)**  
  Biblioteca especializada com:
  - Geração de XML conforme layouts oficiais.
  - Assinatura digital XML (XMLDSig).
  - Compressão, envio e recebimento das mensagens SOAP.
  - Tratamento de erros técnicos e fiscais.

- **Banco de Dados MySQL**
  - Controle de lotes enviados.
  - Controle de recibos e protocolos.
  - Logs de integração (request/response resumidos).
  - Parametrização de webservices por UF e ambiente.

- **Infraestrutura de Rede**
  - Firewall liberando tráfego **HTTPS (porta 443)** para os domínios da SEFAZ/SVRS.
  - Certificados raiz instalados no sistema operacional.
  - Sincronização de horário via NTP (requisito para certificados).

### 3.2 Diagrama Lógico Simplificado

```text
Usuário → Front-end → Backend PHP → Camada SEFAZ → WebService SEFAZ
                                   ↓
                               MySQL (lotes, protocolos, logs)
```

---

## 4. Padrões Técnicos Utilizados

### 4.1 Protocolo de Transporte

- **HTTPs / TLS 1.2+**
- Conexão ponto a ponto entre servidor do Emissor e webservices SEFAZ.
- É obrigatório que o servidor permita **client certificate** (mutual TLS).

### 4.2 Formato de Mensagens

- **XML** validado contra os **XSD oficiais** da NF-e (versão 4.00).
- Envelopado em **SOAP 1.2** (operações síncronas/assíncronas).

### 4.3 Certificado Digital

- Tipos suportados inicialmente:
  - **A1 (arquivo .P12 / .PFX)** – recomendado para servidor.
- Responsabilidades:
  - Assinatura do XML da NF-e.
  - Autenticação mútua com o WebService SEFAZ.

Requisitos técnicos:

- O certificado + cadeia (CA intermediária) devem estar disponíveis para:
  - Biblioteca de assinatura XML.
  - Biblioteca de transporte HTTPs.
- Proteção:
  - Armazenar o arquivo criptografado em disco.
  - Senha de acesso ao certificado deve ser armazenada em configuração segura (ex.: variáveis de ambiente, arquivo .env fora do repositório git, secret manager).

### 4.4 Assinatura Digital (XMLDSig)

- Padrão: **XML Signature** com algoritmo:
  - RSA-SHA1 ou RSA-SHA256 (conforme layout vigente).
- Elementos assinados:
  - `infNFe` (conteúdo da NF-e).
  - `infEvento` (para eventos como cancelamento, CC-e).
- È obrigatório incluir a tag `<Signature>` imediatamente após o elemento assinado.

---

## 5. Ambientes SEFAZ

### 5.1 Ambientes Lógicos

- **Homologação**
  - Usado para testes com numeração própria.
  - Não possui validade fiscal.
- **Produção**
  - Gera documentos fiscais válidos.
  - Requer certificação, numeração oficial e autorização da contabilidade.

### 5.2 Parametrização por UF

Cada Estado possui **URLs específicas** para os serviços da NF-e, podendo delegar operações a:

- SEFAZ própria (ex.: SP, MG, etc.).
- SEFAZ Virtual (SVRS/SVAN/SVC) – dependendo da operação.

O sistema **não deve fixar URLs em código**. Elas devem ser lidas de:

- Tabela de **configuração de webservices** no MySQL, contendo:
  - UF
  - Ambiente (`homologacao` / `producao`)
  - Serviço (`NFeAutorizacao`, `NFeRetAutorizacao`, etc.)
  - URL
  - Versão do serviço (ex.: `4.00`)

Exemplo de registro (conceitual):

| UF | Ambiente    | Serviço             | Versão | URL                                                                 |
|----|-------------|---------------------|--------|---------------------------------------------------------------------|
| SP | homologacao | NFeAutorizacao     | 4.00   | https://homologacao.nfe.fazenda.sp.gov.br/ws/nfeautorizacao4.asmx   |

---

## 6. Fluxos de Integração Principais

### 6.1 Emissão de NF-e (Autorização)

Fluxo assíncrono em lote:

1. **Montagem da NF-e**
   - Backend gera os dados fiscais (emitente, destinatário, produtos, impostos).
   - Camada de integração monta o XML `NFe` conforme layout 4.00.

2. **Validação Estrutural**
   - Validar XML contra XSD da SEFAZ (NF-e, imposto, total, etc.).
   - Bloquear envio caso haja erros de schema.

3. **Assinatura**
   - Assinar o nó `infNFe` com o certificado digital do emitente.

4. **Envio em Lote**
   - Montar XML de envio (`enviNFe`) com:
     - `idLote`
     - Uma ou mais `NFe` assinadas.
   - Enviar via serviço **NFeAutorizacao** (SOAP).
   - Receber resposta com:
     - Código de status (ex.: 103 – Lote recebido).
     - `nRec` – número do recibo do lote.

5. **Persistência**
   - Gravar em MySQL:
     - XML da NF-e.
     - XML de envio de lote.
     - Número do recibo (`nRec`).
     - Status atual (ex.: `AGUARDANDO_PROCESSAMENTO`).

6. **Consulta do Recibo de Lote**
   - Aguardar intervalo configurável (ex.: 3–5 segundos).
   - Chamar serviço **NFeRetAutorizacao** com o `nRec`.
   - Interpretar retorno:
     - **Autorizada** – cStat 100/150 com protocolo.
     - **Rejeitada** – códigos de erro (ex.: 204, 218, etc.).
     - **Em processamento** – reconsultar após novo intervalo.

7. **Atualização de Estado**
   - Se **autorizada**:
     - Persistir protocolo (`protNFe`) no banco.
     - Gerar **XML final autorizado** (NF-e + protocolo).
     - Atualizar status para `AUTORIZADA`.
   - Se **rejeitada**:
     - Atualizar status para `REJEITADA`.
     - Associar mensagem de erro para exibição ao usuário.
   - Se **em processamento**:
     - Manter em fila de reconsulta até timeout configurado.

8. **Geração de DANFe**
   - Para NF-e autorizada, liberar geração/impressão do DANFe (PDF/HTML).

### 6.2 Consulta de Situação da NF-e

Usada para confirmar situação na SEFAZ:

1. Backend solicita consulta por chave de acesso (`chNFe`).
2. Camada de integração monta XML `consSitNFe`.
3. Envia para serviço **NFeConsultaProtocolo**.
4. Resposta retorna:
   - Situação atual (autorizada, cancelada, denegada, inexistente).
   - Eventuais eventos vinculados.
5. Sistema atualiza o status interno se houver divergência.

### 6.3 Inutilização de Numeração

Usado quando uma faixa de numeração não será utilizada.

1. Usuário informa:
   - Série.
   - Número inicial e final.
   - Justificativa.
2. Backend monta XML `inutNFe`.
3. Assinatura do nó `infInut`.
4. Envio para serviço **NFeInutilizacao**.
5. SEFAZ retorna protocolo de inutilização ou motivo da rejeição.
6. Sistema registra a faixa como inutilizada na base de dados.

### 6.4 Cancelamento de NF-e

1. Pré-requisitos:
   - NF-e deve estar **autorizada**.
   - Deve estar dentro do prazo de cancelamento da UF.
2. Usuário informa:
   - Justificativa.
   - Protocolo da NF-e (já cadastrado no sistema).
3. Backend monta XML de evento `envEvento` (tipo `110111` – cancelamento).
4. Assinatura do nó `infEvento`.
5. Envio para serviço **RecepcaoEvento**.
6. Resposta:
   - Se sucesso (ex.: cStat 135), o evento é vinculado à NF-e.
   - Atualizar status da NF-e para `CANCELADA`.
   - Persistir XML do evento + protocolo.

### 6.5 Carta de Correção Eletrônica (CC-e)

1. Usuário informa:
   - Chave da NF-e.
   - Texto da correção seguindo as regras fiscais.
2. Backend monta evento `envEvento` tipo `110110`.
3. Assina e envia via **RecepcaoEvento**.
4. Atualiza histórico de eventos da NF-e e a situação da CC-e.

### 6.6 Consulta Cadastro Contribuinte

Usado para validar situação cadastral de clientes/fornecedores.

1. Informar:
   - UF.
   - CNPJ/CPF/IE.
2. Backend monta XML `ConsCad`.
3. Envia para serviço **ConsultaCadastro**.
4. Retorno indica:
   - Situação cadastral (ativo, baixado, suspenso).
   - Dados de endereço.
5. Sistema pode sugerir atualização automática do cadastro.

### 6.7 Distribuição de DF-e (Download de XML)

Usado para obter XML de NF-e em que o CNPJ é parte (emitente/destinatário).

1. Backend monta XML `distDFeInt`.
2. Chama serviço da **Distribuição de DF-e** na RFB (Receita Federal).
3. Interpreta os pacotes retornados:
   - NSU atual.
   - Documentos disponíveis (NFe, eventos).
4. Persistir XMLs recebidos e vincular a clientes/fornecedores.

---

## 7. Estrutura de Mensagens XML (Visão Técnica)

### 7.1 Padrão de Namespaces

- `xmlns="http://www.portalfiscal.inf.br/nfe"`
- Outros namespaces podem ser utilizados em eventos e distribuições.

### 7.2 Exemplo Simplificado – Envio de Lote (`enviNFe`)

> Obs.: Estrutura simplificada para fins de documentação interna.

```xml
<enviNFe versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe">
  <idLote>000000000000001</idLote>
  <indSinc>0</indSinc>
  <NFe>
    <!-- Conteúdo da NF-e assinada -->
  </NFe>
</enviNFe>
```

### 7.3 Exemplo Simplificado – Consulta Recibo (`consReciNFe`)

```xml
<consReciNFe versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe">
  <tpAmb>2</tpAmb>        <!-- 2 = Homologação, 1 = Produção -->
  <nRec>351000000000001</nRec>
</consReciNFe>
```

### 7.4 Tipos de Ambiente

- `tpAmb = 1` → Produção  
- `tpAmb = 2` → Homologação

Este campo deve ser parametrizável por empresa.

---

## 8. Camada de Integração no Sistema (PHP)

### 8.1 Organização de Pacotes / Namespaces

Sugestão de estrutura:

```text
src/
  Sefaz/
    Http/
      SoapClientFactory.php
    Xml/
      NFeBuilder.php
      NFeValidator.php
      XmlSigner.php
    Services/
      NFeAutorizacaoService.php
      NFeRetAutorizacaoService.php
      NFeInutilizacaoService.php
      NFeConsultaService.php
      EventoService.php
      DistDFeService.php
    Config/
      WebserviceConfigRepository.php
      CertificateConfig.php
```

### 8.2 Responsabilidades principais

- **NFeBuilder**
  - Monta o XML puro a partir de objetos de domínio (Pedido, Empresa, Cliente).
- **NFeValidator**
  - Valida o XML contra XSD e regras sintáticas básicas.
- **XmlSigner**
  - Aplica a assinatura digital usando certificado A1.
- **Services (por operação)**
  - Encapulam a lógica de:
    - Montar mensagem SOAP.
    - Invocar `SoapClient`.
    - Interpretar resposta.
    - Mapear códigos da SEFAZ para status internos.

### 8.3 Padrões de Erro e Exceções

- Criar exceções específicas:
  - `SefazCommunicationException`
  - `SefazValidationException`
  - `SefazBusinessException`
- Todas as chamadas de integração devem:
  - Registrar logs de entrada/saída (respeitando LGPD).
  - Retornar ao backend um _DTO_ com:
    - `sucesso` (bool)
    - `codigo`
    - `mensagem`
    - `dadosBrutos` (XML da resposta, opcional e filtrado).

---

## 9. Segurança, Logs e Compliance

### 9.1 Segurança

- Acesso à tela de configuração SEFAZ restrito a usuários com papel de **administrador fiscal**.
- Senhas de certificados nunca devem aparecer em logs.
- Armazenar apenas:
  - `serial` do certificado.
  - Data de validade.
  - Indicador de tipo (A1/A3).

### 9.2 Logs Técnicos

- Gravar:
  - Data/hora da chamada.
  - Serviço chamado.
  - Ambiente (homologação/produção).
  - UF.
  - Resultado (sucesso, erro de comunicação, erro fiscal).
  - Código e mensagem da SEFAZ.
- Não gravar:
  - CPF completo, e-mails, telefones (anonimizar quando possível).
  - Conteúdo completo do XML em logs de produção (usar storage protegido se necessário).

### 9.3 LGPD e Sigilo Fiscal

- XML das NF-e contém dados sensíveis (endereços, CPFs, valores).
- Definir política de retenção:
  - Tempo mínimo requerido pela legislação fiscal.
  - Procedimentos de backup e criptografia em repouso.

---

## 10. Parametrização de Integração

### 10.1 Tabela de Configuração de Certificados

Campos sugeridos:

- `id_empresa`
- `tipo_certificado` (A1/A3)
- `caminho_arquivo` (A1)
- `senha_criptografada`
- `data_validade`
- `hash_integridade` (opcional)

### 10.2 Tabela de WebServices

- `id`
- `uf`
- `ambiente`
- `servico`
- `versao`
- `url`
- `ativo` (S/N)

### 10.3 Tabela de Controle de Lotes

- `id_lote`
- `id_empresa`
- `numero_lote_sefaz`
- `tp_ambiente`
- `status` (aguardando, processando, autorizado, rejeitado)
- `n_rec`
- `data_envio`
- `data_ultima_consulta`
- `mensagem_erro`

---

## 11. Estratégia de Timeout, Retentativas e Contingência

### 11.1 Timeout de Comunicação

- Timeout de conexão: **10–20 segundos**.
- Timeout de resposta: **30–60 segundos**.
- Configuráveis via arquivo `.env` ou tabela de configurações.

### 11.2 Retentativas Automáticas

- Em erros de rede temporários (timeouts, falha de DNS, HTTP 500):
  - Realizar até **3 retentativas** com backoff exponencial (ex.: 2s, 4s, 8s).
- Registrar nos logs cada tentativa e resultado.

### 11.3 Contingência

Mesmo que a emissão em contingência (FS-DA, EPEC, etc.) não seja implementada de início, o sistema deve:

- Sinalizar claramente ao usuário quando o problema é:
  - **Erro na SEFAZ** (indisponibilidade detectada pelos códigos de status).
  - **Erro interno** (rede local, certificado, etc.).
- Permitir exportar a NF-e montada para eventual emissão por outro sistema, se necessário (como plano B operacional).

---

## 12. Monitoramento e Indicadores

### 12.1 Indicadores Técnicos

- Tempo médio de:
  - Envio de lote.
  - Recebimento de protocolo.
- Taxa de:
  - NF-e autorizadas.
  - NF-e rejeitadas.
  - Erros de comunicação por dia.

### 12.2 Painel de Monitoramento

O sistema deverá possuir, em versões futuras, um painel com:

- Últimas NF-e emitidas.
- Situação por UF/ambiente (percentual de erros).
- Alertas de:
  - Certificado prestes a expirar.
  - Falha de comunicação recorrente com determinada UF.

---

## 13. Checklists de Integração

### 13.1 Checklist – Preparação de Ambiente

- [ ] Servidor com PHP e extensões necessárias instaladas (OpenSSL, SOAP, cURL).
- [ ] Certificado A1 instalado e testado.
- [ ] Cadeia de certificados atualizada.
- [ ] Firewall liberando destino para URLs da SEFAZ/SVRS.
- [ ] Hora do servidor sincronizada via NTP.

### 13.2 Checklist – Homologação

- [ ] Emissão de NF-e de teste autorizada.
- [ ] Cancelamento de NF-e de teste autorizado.
- [ ] Inutilização de numeração homologada.
- [ ] Consulta cadastro retornando dados válidos.
- [ ] Distribuição DF-e retornando documentos para CNPJ de teste.
- [ ] Logs técnicos revisados pela equipe.

### 13.3 Checklist – Go Live (Produção)

- [ ] Importação do certificado de produção.
- [ ] Ajuste de URLs para ambiente de produção.
- [ ] Revisão de parâmetros fiscais com contabilidade.
- [ ] Emissão de NF-e de produção com valor simbólico validada.
- [ ] Backup dos XMLs e do banco de dados configurado.

---

## 14. Manutenção e Evolução

- Alterações de versão de layout (ex.: 4.00 → 4.10) devem seguir processo:
  1. Análise do novo manual técnico SEFAZ.
  2. Atualização de XSDs.
  3. Adequação dos builders de XML.
  4. Atualização de testes automatizados de integração.
- Novos serviços (ex.: NFC-e, CT-e) devem reutilizar ao máximo:
  - Infraestrutura de certificados.
  - Clientes SOAP.
  - Camada de logs e monitoramento.

---

Este **Guia de Integração SEFAZ (Técnico)** é o documento de referência para qualquer modificação que envolva comunicação com a SEFAZ.  
Toda alteração significativa na integração deve ser registrada em **controle de versão** (Git) e vinculada a uma _issue_ ou _change request_ de arquitetura.
